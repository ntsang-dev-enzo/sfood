<?php

namespace App\Filament\Resources\RecipeCategoryResource\Pages;

use App\Filament\Resources\RecipeCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecipeCategory extends EditRecord
{
    protected static string $resource = RecipeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
