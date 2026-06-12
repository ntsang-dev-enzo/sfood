<?php

namespace App\Filament\Resources\RecipeCategoryResource\Pages;

use App\Filament\Resources\RecipeCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecipeCategories extends ListRecords
{
    protected static string $resource = RecipeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
