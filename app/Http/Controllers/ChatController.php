<?php

namespace App\Http\Controllers;

use App\Models\AiChatHistory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ChatController extends Controller
{
    // Number of messages kept in DB per session / user
    private const MAX_HISTORY_DB   = 60;

    // Number of messages sent to AI for context
    private const MAX_HISTORY_AI   = 10;

    // Number of messages returned to frontend on initial load
    private const MAX_HISTORY_LOAD = 30;

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function sessionId(): string
    {
        if (! Session::has('ai_chat_session_id')) {
            Session::put('ai_chat_session_id', (string) str()->uuid());
        }

        return Session::get('ai_chat_session_id');
    }

    private function userId(): ?int
    {
        return Auth::id();
    }

    /**
     * Persist a single message row, then prune old rows when over limit.
     */
    private function saveMessage(string $role, string $content, ?string $htmlContent = null): void
    {
        $userId    = $this->userId();
        $sessionId = $userId ? null : $this->sessionId();

        AiChatHistory::create([
            'user_id'      => $userId,
            'session_id'   => $sessionId,
            'role'         => $role,
            'content'      => $content,
            'html_content' => $htmlContent,
        ]);

        // Prune oldest rows when over limit
        $query = AiChatHistory::forContext($userId, $sessionId);
        $count = $query->count();

        if ($count > self::MAX_HISTORY_DB) {
            $ids = (clone $query)
                ->orderBy('created_at')
                ->limit($count - self::MAX_HISTORY_DB)
                ->pluck('id');

            AiChatHistory::whereIn('id', $ids)->delete();
        }
    }

    /**
     * Fetch the last N rows for the current context.
     */
    private function getHistory(int $limit): \Illuminate\Support\Collection
    {
        $userId    = $this->userId();
        $sessionId = $userId ? null : $this->sessionId();

        return AiChatHistory::forContext($userId, $sessionId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    // -------------------------------------------------------
    // Routes
    // -------------------------------------------------------

    /**
     * GET /ai-chat/history
     * Returns the last MAX_HISTORY_LOAD messages for the current context.
     */
    public function history(): JsonResponse
    {
        $rows = $this->getHistory(self::MAX_HISTORY_LOAD);

        return response()->json(
            $rows->map(fn ($r) => [
                'role'         => $r->role,
                'content'      => $r->content,
                'html_content' => $r->html_content,
            ])
        );
    }

    /**
     * DELETE /ai-chat/history
     * Clears all messages for the current context.
     */
    public function clearHistory(): JsonResponse
    {
        $userId    = $this->userId();
        $sessionId = $userId ? null : $this->sessionId();

        AiChatHistory::forContext($userId, $sessionId)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * POST /ai-chat
     * Main chat endpoint.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userMessage = $request->input('message');

        // Build AI context from DB (last MAX_HISTORY_AI messages)
        $dbHistory = $this->getHistory(self::MAX_HISTORY_AI);
        $aiMessages = $dbHistory->map(fn ($r) => [
            'role'    => $r->role,
            'content' => $r->content,
        ])->values()->all();

        // Append current user message
        $aiMessages[] = ['role' => 'user', 'content' => $userMessage];

        // Save user message to DB immediately
        $this->saveMessage('user', $userMessage, htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8'));

        try {
            $response = Http::timeout(60)->post('http://127.0.0.1:5000/chat-rag', [
                'messages' => $aiMessages,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (! empty($data['success']) && ! empty($data['reply'])) {
                    $replyText   = $data['reply'];
                    $redirectUrl = null;

                    // Check for [REDIRECT:slug] tag
                    if (preg_match('/\[REDIRECT:([^\]]+)\]/i', $replyText, $matches)) {
                        $slug = trim($matches[1]);

                        if (\App\Models\Product::where('slug', $slug)->exists()) {
                            $redirectUrl = route('products.show', $slug);
                        } elseif (\App\Models\Recipe::where('slug', $slug)->exists()) {
                            $redirectUrl = route('recipes.show', $slug);
                        }

                        $replyText = str_replace($matches[0], '', $replyText);
                    }

                    // Render markdown → HTML
                    $html = \Illuminate\Support\Str::markdown($replyText);

                    // Append recipe cards
                    $recipesData = $data['recipes'] ?? [];
                    if (! empty($recipesData)) {
                        $topRecipeSlugs = collect($recipesData)->take(2)->pluck('slug')->toArray();
                        $recipes        = \App\Models\Recipe::whereIn('slug', $topRecipeSlugs)->get();

                        if ($recipes->count() > 0) {
                            $html .= '<div class="mt-4 mb-2">';
                            $html .= '<p class="text-xs font-bold text-earth-500 uppercase mb-2">Gợi ý công thức</p>';
                            $html .= '<div class="grid grid-cols-2 gap-2">';
                            foreach ($recipes as $recipe) {
                                $html .= view('partials.recipe-card', ['recipe' => $recipe])->render();
                            }
                            $html .= '</div></div>';
                        }
                    }

                    // Append product cards
                    $results = $data['results'] ?? [];
                    if (! empty($results)) {
                        $topProductIds = collect($results)->take(4)->pluck('id')->toArray();
                        $products      = Product::whereIn('id', $topProductIds)->get();

                        if ($products->count() > 0) {
                            $html .= '<div class="mt-4">';
                            $html .= '<p class="text-xs font-bold text-earth-500 uppercase mb-2">Sản phẩm gợi ý</p>';
                            $html .= '<div class="grid grid-cols-2 gap-2">';
                            foreach ($products as $product) {
                                $html .= '<div class="flex flex-col bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-cream-200 dark:border-gray-700">';
                                $html .= view('partials.product-card', ['product' => $product])->render();
                                $url  = route('products.show', $product->slug);
                                $html .= "<a href='{$url}' class='block text-center bg-leaf-500 text-white text-xs font-bold py-2 mx-2 mb-2 rounded-lg hover:bg-leaf-600 shadow-md hover:-translate-y-0.5 transition'>👉 Mua ngay</a>";
                                $html .= '</div>';
                            }
                            $html .= '</div></div>';
                        }
                    }

                    // Save assistant reply to DB
                    $this->saveMessage('assistant', $replyText, $html);

                    return response()->json([
                        'reply'    => $html,
                        'redirect' => $redirectUrl,
                    ]);
                }
            }

            $fallback = '<p class="text-sm">Xin lỗi, hệ thống AI đang nâng cấp. Vui lòng thử lại sau nhé!</p>';
            $this->saveMessage('assistant', 'Xin lỗi, hệ thống AI đang nâng cấp.', $fallback);

            return response()->json(['reply' => $fallback]);

        } catch (\Exception $e) {
            $errHtml = '<p class="text-sm text-red-500">Đã xảy ra lỗi kết nối đến AI. Vui lòng thử lại sau.</p>';

            return response()->json(['reply' => $errHtml]);
        }
    }

    /**
     * POST /ai-chat/image
     */
    public function chatImage(Request $request, \App\Services\AiSearchService $aiSearch): JsonResponse
    {
        $request->validate([
            'ai_image' => 'required|image|max:5120',
        ]);

        try {
            $data  = $aiSearch->searchGlobalByImage($request->file('ai_image'));
            $label = $data['label'] ?? 'Hình ảnh';
            $html  = '<p class="text-sm mb-2">Tôi nhận ra đây có vẻ là <strong>' . htmlspecialchars($label) . '</strong>. Dưới đây là các kết quả tìm kiếm phù hợp:</p>';

            $recipesData = collect($data['recipes'] ?? [])->take(2);
            if ($recipesData->count() > 0) {
                $html .= '<div class="mt-4 mb-2 space-y-2">';
                $html .= '<p class="text-xs font-bold text-earth-500 uppercase">Công thức gợi ý</p>';
                foreach ($recipesData as $recipe) {
                    $url = route('recipes.show', $recipe->slug);
                    $img = asset('storage/' . $recipe->thumbnail);
                    $html .= <<<HTML
                    <a href="{$url}" class="flex items-center gap-3 bg-white border border-earth-200 rounded-lg p-2 hover:bg-orange-50 transition">
                        <img src="{$img}" class="w-12 h-12 object-cover rounded-md border border-gray-200">
                        <div>
                            <p class="text-sm font-semibold text-earth-800 line-clamp-1">{$recipe->title}</p>
                        </div>
                    </a>
HTML;
                }
                $html .= '</div>';
            }

            $productsData = collect($data['products'] ?? [])->take(3);
            if ($productsData->count() > 0) {
                $html .= '<div class="mt-4 space-y-2">';
                $html .= '<p class="text-xs font-bold text-earth-500 uppercase">Sản phẩm liên quan</p>';
                foreach ($productsData as $product) {
                    $url   = route('products.show', $product->slug);
                    $img   = asset('storage/' . $product->thumbnail);
                    $price = number_format($product->minPrice());
                    $html .= <<<HTML
                    <a href="{$url}" class="flex items-center gap-3 bg-white border border-gray-100 rounded-lg p-2 hover:bg-green-50 transition">
                        <img src="{$img}" class="w-12 h-12 object-cover rounded-md border border-gray-200">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 line-clamp-1">{$product->name}</p>
                            <p class="text-xs font-bold text-green-600">{$price}đ</p>
                        </div>
                    </a>
HTML;
                }
                $html .= '</div>';
            }

            // Save image search as a pair of messages
            $this->saveMessage('user', "[Hình ảnh] {$label}", '<p class="text-sm italic text-gray-500">[Đã gửi hình ảnh]</p>');
            $this->saveMessage('assistant', $label, $html);

            return response()->json(['reply' => $html, 'redirect' => null]);

        } catch (\Exception $e) {
            return response()->json([
                'reply' => '<p class="text-sm text-red-500">Lỗi phân tích hình ảnh: ' . htmlspecialchars($e->getMessage()) . '</p>',
            ]);
        }
    }
}
