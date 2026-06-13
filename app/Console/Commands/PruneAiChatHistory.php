<?php

namespace App\Console\Commands;

use App\Models\AiChatHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneAiChatHistory extends Command
{
    protected $signature   = 'chat:prune-ai {--days=90 : Số ngày lưu trữ}';
    protected $description = 'Xóa lịch sử AI chatbot cũ hơn N ngày (mặc định 90 ngày)';

    public function handle(): int
    {
        $days      = (int) $this->option('days');
        $cutoff    = Carbon::now()->subDays($days);

        $deleted = AiChatHistory::where('created_at', '<', $cutoff)->delete();

        $this->info("[AI Chat] Đã xóa {$deleted} messages cũ hơn {$days} ngày (trước {$cutoff->toDateString()}).");

        return self::SUCCESS;
    }
}
