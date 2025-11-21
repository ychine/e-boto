<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(['BSIT', 'BSA', 'BSLEA'])
            ->each(fn (string $name) => Course::query()->updateOrCreate(['name' => $name]));
    }
}
