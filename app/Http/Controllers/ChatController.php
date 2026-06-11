<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array'
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);

        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            // Call AI service to use Local LLM RAG
            $response = Http::timeout(60)->post('http://127.0.0.1:5000/chat-rag', [
                'messages' => $messages
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['success']) && !empty($data['reply'])) {
                    $replyText = $data['reply'];
                    $redirectUrl = null;

                    // Check for [REDIRECT:slug] tag
                    if (preg_match('/\[REDIRECT:([^\]]+)\]/i', $replyText, $matches)) {
                        $slug = trim($matches[1]);
                        
                        // Check if it's a product
                        if (\App\Models\Product::where('slug', $slug)->exists()) {
                            $redirectUrl = route('products.show', $slug);
                        } else if (\App\Models\Recipe::where('slug', $slug)->exists()) {
                            $redirectUrl = route('recipes.show', $slug);
                        }
                        
                        // Remove the tag from the reply
                        $replyText = str_replace($matches[0], '', $replyText);
                    }

                    // Parse markdown to HTML
                    $html = \Illuminate\Support\Str::markdown($replyText);
                    
                    // If there are recipes, we can append recipe cards first
                    $recipesData = $data['recipes'] ?? [];
                    if (!empty($recipesData)) {
                        $topRecipeSlugs = collect($recipesData)->take(2)->pluck('slug')->toArray();
                        $recipes = \App\Models\Recipe::whereIn('slug', $topRecipeSlugs)->get();

                        if ($recipes->count() > 0) {
                            $html .= '<div class="mt-4 mb-2 space-y-2">';
                            $html .= '<p class="text-xs font-bold text-earth-500 uppercase">Gợi ý món ăn</p>';
                            foreach ($recipes as $recipe) {
                                $url = route('recipes.show', $recipe->slug);
                                $img = asset('storage/' . $recipe->thumbnail);
                                $html .= <<<HTML
                                <a href="{$url}" class="flex items-center gap-3 bg-white border border-earth-200 rounded-lg p-2 hover:bg-orange-50 transition">
                                    <img src="{$img}" class="w-12 h-12 object-cover rounded-md border border-gray-200">
                                    <div>
                                        <p class="text-sm font-semibold text-earth-800 line-clamp-1">{$recipe->title}</p>
                                        <p class="text-xs text-earth-500 line-clamp-1">{$recipe->description}</p>
                                    </div>
                                </a>
HTML;
                            }
                            $html .= '</div>';
                        }
                    }

                    // If there are products, we can also append product cards
                    $results = $data['results'] ?? [];
                    if (!empty($results)) {
                        $topProductIds = collect($results)->take(3)->pluck('id')->toArray();
                        $products = Product::whereIn('id', $topProductIds)->get();

                        if ($products->count() > 0) {
                            $html .= '<div class="mt-4 space-y-2">';
                            $html .= '<p class="text-xs font-bold text-earth-500 uppercase">Nguyên liệu</p>';
                            foreach ($products as $product) {
                                $url = route('products.show', $product->slug);
                                $img = asset('storage/' . $product->thumbnail);
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
                    }

                    return response()->json([
                        'reply' => $html,
                        'redirect' => $redirectUrl
                    ]);
                }
            }

            return response()->json([
                'reply' => '<p class="text-sm">Xin lỗi, hệ thống AI đang nâng cấp. Vui lòng thử lại sau nhé!</p>'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'reply' => '<p class="text-sm text-red-500">Đã xảy ra lỗi kết nối đến AI. Vui lòng thử lại sau.</p>'
            ]);
        }
    }
}
