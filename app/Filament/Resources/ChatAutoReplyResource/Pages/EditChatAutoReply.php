<?php

namespace App\Filament\Resources\ChatAutoReplyResource\Pages;

use App\Filament\Resources\ChatAutoReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChatAutoReply extends EditRecord
{
    protected static string $resource = ChatAutoReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Xóa'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
