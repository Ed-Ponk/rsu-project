<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'vehicle_model_id',
        'color_id',
        'plate',
        'year',
        'capacity',
        'image',
    ];

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        return asset('images/no_logo.svg');
    }

    /**
     * Apply the listing filters (search by plate and catalog filters).
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereRaw(
                    "plate LIKE ? ESCAPE '!'",
                    ['%' . $this->escapeLike($search) . '%'],
                ),
            )
            ->when(
                $filters['brand_id'] ?? null,
                fn (Builder $query, $brandId) => $query->whereHas(
                    'vehicleModel',
                    fn (Builder $query) => $query->where('brand_id', $brandId),
                ),
            )
            ->when(
                $filters['vehicle_model_id'] ?? null,
                fn (Builder $query, $modelId) => $query->where('vehicle_model_id', $modelId),
            )
            ->when(
                $filters['color_id'] ?? null,
                fn (Builder $query, $colorId) => $query->where('color_id', $colorId),
            );
    }

    /**
     * Escape LIKE wildcards so they are matched literally.
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}
