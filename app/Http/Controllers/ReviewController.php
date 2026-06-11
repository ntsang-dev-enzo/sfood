<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', auth()->id())
            ->where('order_status', 'delivered')
            ->firstOrFail();

        // Check if user already reviewed this product
        $existing = ProductReview::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        $attachments = $existing ? ($existing->attachments ?? []) : [];
        if ($request->hasFile('attachments')) {
            $attachments = []; // replace old attachments
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('reviews', 'public');
                $attachments[] = $path;
            }
        }

        ProductReview::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
            ],
            [
                'order_id' => $request->order_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'attachments' => $attachments,
            ]
        );

        return back()->with('success', 'Cảm ơn bạn! Đánh giá đã được lưu.');
    }
}
