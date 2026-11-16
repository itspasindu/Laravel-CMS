<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->string('excerpt', 500)->nullable()->after('body');
            $table->boolean('is_featured')->default(false)->after('published_at');
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->string('category')->default('general')->after('body');
            $table->boolean('is_featured')->default(false)->after('is_published');
            $table->timestamp('pinned_until')->nullable()->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['slug', 'excerpt', 'is_featured']);
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->dropColumn(['slug', 'category', 'is_featured', 'pinned_until']);
        });
    }
};
