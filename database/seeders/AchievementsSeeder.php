<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $achievements = [
            ['title' => 'Silent Hacker', 'badge' => 'uploads/badges/silent_hacker.png', 'points' => '10'],
            ['title' => 'CPU Killer', 'badge' => 'uploads/badges/cpu_killer.png', 'points' => '500'],
            ['title' => 'Deadeye', 'badge' => 'uploads/badges/deadeye.png', 'points' => '1000'],
        ];

        Achievement::insert($achievements);
    }
}
