<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động xóa lịch sử AI chatbot cũ hơn 90 ngày — chạy lúc 2:00 AM mỗi ngày
Schedule::command('chat:prune-ai --days=90')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chat-prune.log'));

// Tự động xóa lịch sử CSKH cũ hơn 180 ngày — chạy lúc 2:30 AM mỗi ngày
Schedule::command('chat:prune-cskh --days=180')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chat-prune.log'));
