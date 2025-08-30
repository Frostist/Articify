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

        // Generate realistic article titles
        $titleTemplates = [
            'The Future of {technology} in {year}',
            'How {company} is Revolutionizing {industry}',
            '{number} Ways to Improve Your {skill}',
            'Why {topic} Matters More Than Ever',
            'The Complete Guide to {subject}',
            '{company}\'s Latest {product} Release',
            'Understanding {concept} in Modern {field}',
            'Top {number} {category} Trends for {year}',
            'Breaking Down {complex_topic}',
            'The Impact of {technology} on {industry}',
        ];

        $title = $this->faker->randomElement($titleTemplates);
        $title = str_replace(
            ['{technology}', '{year}', '{company}', '{industry}', '{number}', '{skill}', '{topic}', '{subject}', '{product}', '{concept}', '{field}', '{category}', '{complex_topic}'],
            [
                $this->faker->randomElement(['AI', 'Machine Learning', 'Blockchain', 'Cloud Computing', 'IoT', 'Cybersecurity', 'Data Science', 'DevOps', 'Mobile Development', 'Web Development']),
                $this->faker->year(),
                $this->faker->randomElement(['Google', 'Microsoft', 'Apple', 'Amazon', 'Meta', 'Netflix', 'Tesla', 'SpaceX', 'OpenAI', 'Stripe']),
                $this->faker->randomElement(['Healthcare', 'Finance', 'Education', 'Retail', 'Manufacturing', 'Transportation', 'Entertainment', 'Real Estate']),
                $this->faker->numberBetween(5, 25),
                $this->faker->randomElement(['Programming', 'Design', 'Marketing', 'Leadership', 'Communication', 'Problem Solving', 'Time Management']),
                $this->faker->randomElement(['Sustainability', 'Privacy', 'Security', 'Innovation', 'Collaboration', 'Remote Work', 'Digital Transformation']),
                $this->faker->randomElement(['Web Development', 'Data Analysis', 'Project Management', 'User Experience', 'Content Creation', 'Business Strategy']),
                $this->faker->randomElement(['Framework', 'Platform', 'Tool', 'Service', 'Application', 'System']),
                $this->faker->randomElement(['Microservices', 'API Design', 'Database Optimization', 'Performance Tuning', 'Security Protocols', 'Scalability']),
                $this->faker->randomElement(['Software Development', 'Digital Marketing', 'Data Science', 'Product Management', 'User Research', 'Business Intelligence']),
                $this->faker->randomElement(['Technology', 'Business', 'Design', 'Development', 'Marketing', 'Product']),
                $this->faker->randomElement(['Distributed Systems', 'Machine Learning Algorithms', 'Blockchain Technology', 'Cloud Architecture', 'Cybersecurity Threats', 'Data Privacy Regulations']),
            ],
            $title
        );

        // Generate realistic URLs
        $domains = [
            'techcrunch.com',
            'medium.com',
            'dev.to',
            'css-tricks.com',
            'smashingmagazine.com',
            'sitepoint.com',
            'webdesignerdepot.com',
            'alistapart.com',
            'uxplanet.org',
            'uxdesign.cc',
            'nngroup.com',
            'uxmatters.com',
            'uxbooth.com',
            'uxpin.com',
            'uxmovement.com',
        ];

        $domain = $this->faker->randomElement($domains);
        $slug = $this->faker->slug(3, false);
        $url = "https://{$domain}/{$slug}";

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'publication_date' => $publicationDate,
            'url' => $url,
            'read_date' => $readDate,
            'is_missed_day' => false,
        ];
    }

    /**
     * Indicate that this article represents a missed day.
     */
    public function missedDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_missed_day' => true,
        ]);
    }
}
