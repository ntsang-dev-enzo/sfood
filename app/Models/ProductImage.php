<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Jobs\EmbedProductImage;
use App\Jobs\EmbedImage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::created(function (ProductImage $img) {
            // dispatch job to create embedding asynchronously
            try {
                // prefer generic job for consistent embedding records
                EmbedImage::dispatch($img->product_id, $img->id, $img->image_path);
            } catch (\Throwable $e) {
                // ignore dispatch failures
            }
        });

        static::updated(function (ProductImage $img) {
            // if image changed, remove previous embedding and dispatch for new image
            try {
                if ($img->isDirty('image_path')) {
                    // delete any existing embedding for this product image
                    \Illuminate\Support\Facades\DB::table('product_embeddings')
                        ->where('product_id', $img->product_id)
                        ->where('product_image_id', $img->id)
                        ->delete();

                    EmbedImage::dispatch($img->product_id, $img->id, $img->image_path);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        });

        static::deleted(function (ProductImage $img) {
            // remove embedding row for this product image
            try {
                DB::table('product_embeddings')
                    ->where('product_id', $img->product_id)
                    ->where('product_image_id', $img->id)
                    ->delete();
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
}
