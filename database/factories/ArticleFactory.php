<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publicationDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $readDate = $this->faker->dateTimeBetween($publicationDate, 'now');

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(6, true),
            'publication_date' => $publicationDate,
            'url' => $this->faker->url(),
            'read_date' => $readDate,
        ];
    }
}
