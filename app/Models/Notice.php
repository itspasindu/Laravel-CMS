<?php

namespace App\Models;

use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string|null $slug
 * @property string|null $title
 * @property string|null $body
 * @property string|null $category
 * @property string|null $attachment_path
 * @property bool $is_published
 * @property bool $is_featured
 * @property Carbon|null $pinned_until
 */
class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'body',
        'category',
        'attachment_path',
        'is_published',
        'is_featured',
        'pinned_until',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'pinned_until' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Notice $notice): void {
            if (blank($notice->slug) && filled($notice->title)) {
                $notice->slug = static::uniqueSlug($notice->title, $notice->id);
            }

            if (blank($notice->category)) {
                $notice->category = 'general';
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getRouteKey(): mixed
    {
        return filled($this->slug) ? $this->slug : $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null): ?static
    {
        $field ??= $this->getRouteKeyName();

        /** @var static|null $match */
        $match = $this->where($field, $value)
            ->orWhere($this->getKeyName(), $value)
            ->first();

        return $match;
    }

    public function isPinned(): bool
    {
        return $this->pinned_until !== null && $this->pinned_until->isFuture();
    }

    /**
     * @param  Builder<Notice>  $query
     * @return Builder<Notice>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @param  Builder<Notice>  $query
     * @return Builder<Notice>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param  Builder<Notice>  $query
     * @return Builder<Notice>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder->where('title', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%");
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'notice';
        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
