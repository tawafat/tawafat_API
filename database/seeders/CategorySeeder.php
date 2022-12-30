<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Category::create([
            'slug' => 'general',
            'name' => 'عام',
            'description' => 'تصنيف عامل للمهام الغير مصنفة'
        ]);
    }
}
