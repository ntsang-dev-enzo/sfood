<?php

namespace App\Filament\Resources\FlashSaleResource\Pages;

use App\Filament\Resources\FlashSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFlashSale extends CreateRecord
{
    protected static string $resource = FlashSaleResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // When creating, the user selects 'product_id' and 'variant_id' (which is the variant ID)
        $variant = \App\Models\ProductVariant::findOrFail($data['variant_id']);
        
        $variant->update([
            'flash_sale_price' => $data['flash_sale_price'],
            'flash_sale_starts_at' => $data['flash_sale_starts_at'],
            'flash_sale_ends_at' => $data['flash_sale_ends_at'],
            'flash_sale_stock' => $data['flash_sale_stock'] ?? null,
            'flash_sale_sold' => 0,
        ]);

        return $variant;
    }
}
