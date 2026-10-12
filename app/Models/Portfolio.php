<?php

namespace App\Models;

use Database\Factories\PortfolioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'user_id', 'full_name', 'profile_image_path', 'email', 'contact_number', 'address',
    'about_me', 'education', 'skills', 'skill_levels', 'projects', 'experience', 'social_links', 'template',
])]
class Portfolio extends Model
{
    /** @use HasFactory<PortfolioFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Portfolio $portfolio): void {
            $portfolio->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'education' => 'array',
            'skills' => 'array',
            'skill_levels' => 'array',
            'projects' => 'array',
            'experience' => 'array',
            'social_links' => 'array',
        ];
    }
}
