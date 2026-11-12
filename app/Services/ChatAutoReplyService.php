<?php

namespace App\Services;

use App\Models\ChatAutoLog;
use App\Models\ChatAutoReply;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ChatAutoReplyService
{
    /**
     * Ngưỡng điểm tối thiểu để coi là nhận diện được câu hỏi.
     */
    public const THRESHOLD = 0.30;

    /**
     * Thời gian (phút) tạm dừng tự động trả lời sau khi nhân viên đã phản hồi.
     */
    public const HUMAN_ACTIVE_MINUTES = 10;

    public function cacheKey(int $userId): string
    {
        return 'chat_human_requested_'.$userId;
    }

    /**
     * Chuẩn hóa câu hỏi: chữ thường, bỏ dấu tiếng Việt, bỏ ký tự đặc biệt.
     */
    public function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = Str::ascii($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? '';
        $text = preg_replace('/\s+/', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * Chấm điểm một mẫu trả lời.
     *
     * Điểm = tổng độ dài các từ khóa xuất hiện / (tổng độ dài + 6).
     * Cách tính này không bị loãng khi mẫu có nhiều từ khóa, đồng thời
     * ưu tiên từ khóa dài và đặc thù hơn từ khóa ngắn.
     *
     * @return array{0: float, 1: int, 2: int} [điểm, tổng độ dài khớp, số từ khóa khớp]
     */
    public function matchInfo(string $normalized, array $keywords): array
    {
        $matchedLength = 0;
        $matchedCount = 0;

        foreach ($keywords as $keyword) {
            $keyword = $this->normalize((string) $keyword);
            $length = mb_strlen($keyword);

            // Bỏ qua từ khóa quá ngắn để tránh khớp nhầm
            if ($length < 3) {
                continue;
            }

            if ($this->containsKeyword($normalized, $keyword)) {
                $matchedLength += $length;
                $matchedCount++;
            }
        }

        if ($matchedCount === 0) {
            return [0.0, 0, 0];
        }

        $score = min(1.0, $matchedLength / ($matchedLength + 6));

        return [round($score, 4), $matchedLength, $matchedCount];
    }

    /**
     * Kiểm tra từ khóa xuất hiện trong câu theo đúng ranh giới từ.
     *
     * Dùng ranh giới từ để tránh khớp nhầm kiểu "hỏng" nằm trong "không".
     */
    private function containsKeyword(string $normalized, string $keyword): bool
    {
        $pattern = '/(?<![a-z0-9])'.preg_quote($keyword, '/').'(?![a-z0-9])/u';

        return (bool) preg_match($pattern, $normalized);
    }

    public function score(string $normalized, array $keywords): float
    {
        return $this->matchInfo($normalized, $keywords)[0];
    }

    /**
     * Tìm mẫu trả lời khớp nhất. Trả về [mẫu, điểm].
     */
    public function detect(string $message): array
    {
        $normalized = $this->normalize($message);

        if ($normalized === '') {
            return [null, 0.0];
        }

        $best = null;
        $bestScore = 0.0;
        $bestLength = 0;

        foreach (ChatAutoReply::active()->get() as $reply) {
            [$score, $length] = $this->matchInfo($normalized, $reply->keywords ?? []);

            if ($score === 0.0) {
                continue;
            }

            $better = $score > $bestScore
                || ($score === $bestScore && $reply->priority > ($best?->priority ?? 0))
                || ($score === $bestScore && $reply->priority === ($best?->priority ?? 0) && $length > $bestLength);

            if ($better) {
                $best = $reply;
                $bestScore = $score;
                $bestLength = $length;
            }
        }

        return [$bestScore >= self::THRESHOLD ? $best : null, $bestScore];
    }

    /**
     * Danh sách câu hỏi phổ biến để gợi ý cho khách.
     */
    public function suggestions(int $limit = 6)
    {
        return ChatAutoReply::active()
            ->whereNotNull('question')
            ->orderByDesc('hit_count')
            ->orderByDesc('priority')
            ->limit($limit)
            ->get(['id', 'code', 'question', 'group_name']);
    }

    /**
     * Kiểm tra hội thoại có đang do nhân viên phụ trách hay không.
     */
    public function isHumanHandling(int $userId): bool
    {
        if (Cache::get($this->cacheKey($userId))) {
            return true;
        }

        return ChatMessage::where('user_id', $userId)
            ->where('sender', 'admin')
            ->where('created_at', '>=', now()->subMinutes(self::HUMAN_ACTIVE_MINUTES))
            ->exists();
    }

    public function pauseForHuman(int $userId): void
    {
        Cache::put($this->cacheKey($userId), true, now()->addHours(2));
    }

    public function resume(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    /**
     * Sinh câu trả lời tự động cho một tin nhắn của khách.
     *
     * @return array{matched: ?ChatAutoReply, answer: string, score: float, fallback: bool, need_human: bool}
     */
    public function respond(User $user, string $message): array
    {
        if ($this->isHumanHandling($user->id)) {
            return [
                'matched' => null,
                'answer' => '',
                'score' => 0.0,
                'fallback' => false,
                'need_human' => true,
            ];
        }

        [$reply, $score] = $this->detect($message);
        $needHuman = false;

        if ($reply) {
            $answer = $reply->answer;
            $reply->increment('hit_count');
            $needHuman = $reply->code === 'GEN_HUMAN';
        } else {
            $answer = $this->fallbackAnswer();
        }

        if ($needHuman) {
            $this->pauseForHuman($user->id);
        }

        ChatAutoLog::create([
            'user_id' => $user->id,
            'message' => $message,
            'matched_reply_id' => $reply?->id,
            'score' => $score,
            'is_fallback' => $reply === null,
        ]);

        return [
            'matched' => $reply,
            'answer' => $answer,
            'score' => $score,
            'fallback' => $reply === null,
            'need_human' => $needHuman,
        ];
    }

    public function fallbackAnswer(): string
    {
        $reply = ChatAutoReply::active()->where('code', 'FALLBACK')->first();

        if ($reply) {
            $reply->increment('hit_count');

            return $reply->answer;
        }

        return 'Cảm ơn bạn đã nhắn cho SFood. Mình chưa hiểu rõ câu hỏi này. '
            .'Bạn có thể chọn một trong các chủ đề gợi ý bên dưới, hoặc bấm "Gặp nhân viên" để được hỗ trợ trực tiếp.';
    }
}
