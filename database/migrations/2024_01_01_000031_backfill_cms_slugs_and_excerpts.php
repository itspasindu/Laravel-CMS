<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('news') && Schema::hasColumn('news', 'slug')) {
            foreach (DB::table('news')->orderBy('id')->get() as $row) {
                $updates = [];

                if (blank($row->slug ?? null)) {
                    $updates['slug'] = $this->uniqueSlug('news', (string) ($row->title ?: 'news'), (int) $row->id);
                }

                if (Schema::hasColumn('news', 'excerpt') && blank($row->excerpt ?? null) && filled($row->body ?? null)) {
                    $updates['excerpt'] = Str::limit(strip_tags((string) $row->body), 180);
                }

                if ($updates !== []) {
                    DB::table('news')->where('id', $row->id)->update($updates);
                }
            }
        }

        if (Schema::hasTable('notices') && Schema::hasColumn('notices', 'slug')) {
            foreach (DB::table('notices')->orderBy('id')->get() as $row) {
                $updates = [];

                if (blank($row->slug ?? null)) {
                    $updates['slug'] = $this->uniqueSlug('notices', (string) ($row->title ?: 'notice'), (int) $row->id);
                }

                if (Schema::hasColumn('notices', 'category') && blank($row->category ?? null)) {
                    $updates['category'] = 'general';
                }

                if ($updates !== []) {
                    DB::table('notices')->where('id', $row->id)->update($updates);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function uniqueSlug(string $table, string $title, int $id): string
    {
        $base = Str::slug($title) ?: Str::slug($table);
        $slug = $base.'-'.$id;
        $suffix = 1;

        while (DB::table($table)->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $base.'-'.$id.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
};
