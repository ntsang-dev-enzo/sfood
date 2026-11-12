<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAutoReply extends Model
{
    protected $fillable = [
        'code',
        'group_name',
        'question',
        'answer',
        'keywords',
        'priority',
        'is_active',
        'hit_count',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'hit_count' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function groupLabels(): array
    {
        return [
            'order' => 'Đơn hàng',
            'shipping' => 'Vận chuyển',
            'payment' => 'Thanh toán',
            'product' => 'Sản phẩm',
            'promo' => 'Khuyến mãi & điểm thưởng',
            'policy' => 'Đổi trả & khiếu nại',
            'account' => 'Tài khoản & kỹ thuật',
            'general' => 'Chào hỏi & mặc định',
        ];
    }
}
