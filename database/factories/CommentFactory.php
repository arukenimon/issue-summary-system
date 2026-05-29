<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'issue_id'    => Issue::factory(),
            'author_name' => fake()->name(),
            'body'        => fake()->sentence(),
        ];
    }
}
