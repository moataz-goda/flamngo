<?php

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'domain',
    'theme',
    'default_theme',
    'brand_colors',
    'logo',
    'hero_image',
    'tagline',
    'tagline_en',
    'phone',
    'email',
    'address',
    'address_en',
    'currency_symbol',
    'reference_prefix',
    'is_active',
])]
class Shop extends Model
{
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'brand_colors' => 'array',
        ];
    }

    public function brandTheme(): string
    {
        return $this->default_theme ?: $this->theme ?: 'flamingo';
    }

    /**
     * @return array<string, string>
     */
    public function effectiveColors(): array
    {
        return \App\Support\ThemePalette::merge(
            $this->theme ?: 'flamingo',
            is_array($this->brand_colors) ? $this->brand_colors : []
        );
    }

    /**
     * @return array<string, string>
     */
    public function cssColorVariables(): array
    {
        return \App\Support\ThemePalette::cssVariables(
            $this->effectiveColors(),
            $this->theme ?: 'flamingo'
        );
    }

    public function hasCustomColors(): bool
    {
        return is_array($this->brand_colors) && $this->brand_colors !== [];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getLogoUrlAttribute(): string
    {
        $logo = $this->logo;

        if (! $logo) {
            return asset('images/brands/'.$this->brandTheme().'/logo.jpeg');
        }

        if (str_starts_with($logo, 'http')) {
            return $logo;
        }

        if (str_starts_with($logo, '/')) {
            return asset(ltrim($logo, '/'));
        }

        return asset('storage/'.$logo);
    }

    public function getHeroUrlAttribute(): string
    {
        $hero = $this->hero_image;

        if (! $hero) {
            return asset('images/brands/'.$this->brandTheme().'/hero.jpeg');
        }

        if (str_starts_with($hero, 'http')) {
            return $hero;
        }

        if (str_starts_with($hero, '/')) {
            return asset(ltrim($hero, '/'));
        }

        return asset('storage/'.$hero);
    }
}
