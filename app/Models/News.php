<?php

namespace App\Models;

use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'body',
        'excerpt',
        'image_path',
        'published_at',
        'is_featured',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (News $news): void {
            if (blank($news->slug) && filled($news->title)) {
                $news->slug = static::uniqueSlug($news->title, $news->id);
            }

            if (blank($news->excerpt) && filled($news->body)) {
                $news->excerpt = Str::limit(strip_tags($news->body), 180);
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

    /**
     * @param  Builder<News>  $query
     * @return Builder<News>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<News>  $query
     * @return Builder<News>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param  Builder<News>  $query
     * @return Builder<News>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder->where('title', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%");
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'news-item';
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
