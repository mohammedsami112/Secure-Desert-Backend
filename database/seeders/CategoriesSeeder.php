<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['title' => 'Web'],
            ['title' => 'Crypto'],
            ['title' => 'Reverse'],
            ['title' => 'Network'],
            ['title' => 'Forensics'],
            ['title' => 'Binary'],
            ['title' => 'Osint'],
        ];

        Category::insert($categories);
    }
}
