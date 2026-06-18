<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FeedType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (FeedType $feedType): void {
            if (blank($feedType->slug)) {
                $feedType->slug = Str::slug($feedType->name);
            }
        });
    }

    public function formulations(): HasMany
    {
        return $this->hasMany(Formulation::class);
    }
}
