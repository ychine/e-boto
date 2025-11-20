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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('password');
            $table->string('student_id')->unique()->nullable()->after('role');
            $table->string('lrn')->nullable()->after('student_id');
            $table->string('first_name')->nullable()->after('lrn');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
            $table->string('course')->nullable()->after('phone');
            $table->string('section')->nullable()->after('course');
            $table->string('year_level')->nullable()->after('section');
            $table->string('age_group')->nullable()->after('year_level');
            $table->string('gender')->nullable()->after('age_group');
            $table->string('location')->nullable()->after('gender');
            $table->string('status')->default('pending')->after('location');
            $table->string('profile_photo')->nullable()->after('status');
            $table->timestamp('last_login')->nullable()->after('profile_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'student_id',
                'lrn',
                'first_name',
                'last_name',
                'phone',
                'course',
                'section',
                'year_level',
                'age_group',
                'gender',
                'location',
                'status',
                'profile_photo',
                'last_login',
            ]);
        });
    }
};
