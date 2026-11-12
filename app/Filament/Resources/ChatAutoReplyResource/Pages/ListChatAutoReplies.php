<?php

namespace App\Filament\Resources\ChatAutoReplyResource\Pages;

use App\Filament\Resources\ChatAutoReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatAutoReplies extends ListRecords
{
    protected static string $resource = ChatAutoReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm mẫu trả lời'),
        ];
    }
}
