<?php

namespace Database\Seeders;

use App\Models\Article;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        Article::factory(100)->create();

        UserPreference::factory(10)->recycle($users)->create();
    }
}
