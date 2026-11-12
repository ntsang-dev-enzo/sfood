<?php

namespace App\Filament\Resources\ChatAutoReplyResource\Pages;

use App\Filament\Resources\ChatAutoReplyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChatAutoReply extends CreateRecord
{
    protected static string $resource = ChatAutoReplyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
