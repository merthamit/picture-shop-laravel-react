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
        Schema::table('pictures', function (Blueprint $table) {
            // "cloudinary" (private original + signed derived URLs) or "local"
            $table->string('disk')->default('local')->after('file_path');
            $table->string('public_id')->nullable()->after('disk');
            $table->unsignedInteger('width')->nullable()->after('ext');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pictures', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['disk', 'public_id', 'width', 'height']);
        });
    }
};
