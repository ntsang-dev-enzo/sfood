<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneCskhChatHistory extends Command
{
    protected $signature   = 'chat:prune-cskh {--days=180 : Số ngày lưu trữ}';
    protected $description = 'Xóa lịch sử chat CSKH cũ hơn N ngày (mặc định 180 ngày)';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $deleted = ChatMessage::where('created_at', '<', $cutoff)->delete();

        $this->info("[CSKH Chat] Đã xóa {$deleted} messages cũ hơn {$days} ngày (trước {$cutoff->toDateString()}).");

        return self::SUCCESS;
    }
}
