<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Coupon;
use Carbon\Carbon;

class LuckyWheelController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Grant free spin every 24h
        if ($user->last_free_spin_at === null || $user->last_free_spin_at->diffInHours(now()) >= 24) {
            $user->lucky_spins += 1;
            $user->last_free_spin_at = now();
            $user->save();
        }

        // Fetch 8 prizes
        $prizes = \App\Models\WheelPrize::where('is_active', true)->limit(8)->get();
        if ($prizes->count() < 8) {
            $defaultPrizes = collect([
                ['id' => 9991, 'name' => 'Chúc bạn may mắn lần sau', 'type' => 'fixed', 'value' => 0, 'weight' => 500, 'min_order_amount' => 0, 'is_miss' => true],
            ]);
            $prizes = $prizes->concat($defaultPrizes);
        }

        return view('lucky-wheel.index', compact('user', 'prizes'));
    }

    public function spin(Request $request)
    {
        $user = Auth::user();

        if ($user->lucky_spins <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã hết lượt quay. Hãy mua thêm hàng để nhận lượt quay nhé!'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user->decrement('lucky_spins', 1);

            // Lấy 8 phần thưởng đang kích hoạt
            $prizes = \App\Models\WheelPrize::where('is_active', true)
                ->limit(8)
                ->get();

            if ($prizes->count() < 8) {
                // Nếu chưa đủ 8 giải, tự động thêm giải mặc định
                $defaultPrizes = collect([
                    ['id' => 9991, 'name' => 'Chúc bạn may mắn lần sau', 'type' => 'fixed', 'value' => 0, 'weight' => 500, 'min_order_amount' => 0, 'is_miss' => true],
                ]);
                $prizes = $prizes->concat($defaultPrizes);
            }

            // Random spin based on weight
            $totalWeight = $prizes->sum('weight');
            $rand = mt_rand(1, $totalWeight);
            $currentWeight = 0;
            $wonPrize = null;

            foreach ($prizes as $prize) {
                $currentWeight += $prize['weight'];
                if ($rand <= $currentWeight) {
                    $wonPrize = $prize;
                    break;
                }
            }

            // Nếu ô trượt (chúc bạn may mắn lần sau)
            if ($wonPrize['is_miss']) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'prize' => $wonPrize,
                    'coupon' => null,
                    'message' => 'Rất tiếc! ' . $wonPrize['name'] . '. Hãy thử lại vào ngày mai nhé!'
                ]);
            }

            // Create coupon
            $code = 'LUCKY' . strtoupper(\Str::random(6));
            $coupon = Coupon::create([
                'code' => $code,
                'type' => $wonPrize['type'],
                'value' => $wonPrize['value'],
                'min_order_amount' => $wonPrize['min_order_amount'],
                'applicable_category_ids' => $wonPrize['applicable_category_ids'],
                'applicable_product_ids' => $wonPrize['applicable_product_ids'],
                'starts_at' => now(),
                'expires_at' => now()->addDays(7), // Tồn tại 7 ngày
                'usage_limit' => 1,
                'is_active' => true,
                'user_id' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'prize' => $wonPrize,
                'coupon' => $coupon,
                'message' => 'Chúc mừng bạn đã trúng ' . $wonPrize['name'] . '!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.'
            ], 500);
        }
    }
}
