<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Normalize any existing values before applying constraints.
        DB::table('users')
            ->whereNotNull('course')
            ->update(['course' => DB::raw('UPPER(course)')]);

        DB::table('users')
            ->whereNotNull('course')
            ->whereNotIn('course', ['BSIT', 'BSA', 'BSLEA'])
            ->update(['course' => null]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('course')
                ->references('name')
                ->on('courses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['course']);
        });

        Schema::dropIfExists('courses');
    }
};
