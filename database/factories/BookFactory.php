<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Book::class;
    public $authorIds = [];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create();
        return [
            'title' => $faker->words(rand(1, 3), true),
            'description' => $faker->text(),
            'publish_date' => $faker->dateTimeBetween('-100 years', 'now')->format('Y-m-d'),
            'author_id' => $faker->randomElement($this->authorIds),
        ];
    }

    public function withTimestamp()
    {
        $dt = \Carbon\Carbon::now()->toDateTimeString();
        return $this->state([
            'created_at' => $dt,
            'modified_at' => $dt,
        ]);
    }

    public function setAuthorId($ids)
    {
        $this->authorIds = $ids;
        return $this;
    }
}
