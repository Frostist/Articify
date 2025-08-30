<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds. With: php artisan db:seed --class=ArticleSeeder
     */
    public function run(): void
    {
        // Find the user's profile (will@test.com)
        $user = User::where('email', 'will@test.com')->first();
        
        if ($user) {
            // Add more articles to the user's profile (between 30-80 additional articles)
            $additionalArticleCount = rand(30, 80);
            
            Article::factory($additionalArticleCount)
                ->for($user)
                ->create([
                    // Some articles will be missed days (about 15% of articles)
                    'is_missed_day' => function () {
                        return rand(1, 100) <= 15;
                    },
                ]);
                
            $this->command->info("Added {$additionalArticleCount} articles to {$user->name}'s profile ({$user->email})");
        } else {
            $this->command->warn("User with email 'will@test.com' not found. Creating test user...");
            
            // Create the test user if it doesn't exist
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
            
            // Add articles to the new test user
            $articleCount = rand(20, 60);
            
            Article::factory($articleCount)
                ->for($user)
                ->create([
                    'is_missed_day' => function () {
                        return rand(1, 100) <= 15;
                    },
                ]);
                
            $this->command->info("Created test user and added {$articleCount} articles to {$user->name}'s profile ({$user->email})");
        }

        // Also add some articles to other existing users for variety
        $otherUsers = User::where('email', '!=', 'will@test.com')
            ->where('email', '!=', 'test@example.com')
            ->take(3)
            ->get();
            
        foreach ($otherUsers as $otherUser) {
            $articleCount = rand(5, 20);
            
            Article::factory($articleCount)
                ->for($otherUser)
                ->create([
                    'is_missed_day' => function () {
                        return rand(1, 100) <= 15;
                    },
                ]);
        }
    }
}
