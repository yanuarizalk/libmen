<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create();

        return [
            'name' => $faker->name(),
            'bio' => $faker->text(),
            'birth_date' => $faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d'),
        ];
    }
}
