<?php

namespace Database\Factories;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    protected $model = Issue::class;

    public function definition(): array
    {
        return [
            'title'          => fake()->sentence(5),
            'description'    => fake()->paragraph(),
            'priority'       => fake()->randomElement(Issue::priorities()),
            'category'       => fake()->randomElement(Issue::categories()),
            'status'         => fake()->randomElement(Issue::statuses()),
            'summary'        => null,
            'next_action'    => null,
            'summary_status' => 'pending',
            'is_escalated'   => false,
        ];
    }

    public function highPriority(): static
    {
        return $this->state(fn () => [
            'priority'     => 'high',
            'status'       => 'open',
            'is_escalated' => true,
        ]);
    }
}
