<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'logo'];

    public function vehicleModels(): HasMany
    {
        return $this->hasMany(VehicleModel::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return Storage::disk('public')->url($this->logo);
        }

        return asset('images/no_logo.svg');
    }
}
