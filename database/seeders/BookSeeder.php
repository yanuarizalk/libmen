<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use Database\Factories\BookFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorIds = Author::select('id')->pluck('id');
        $factory = BookFactory::new()->setAuthorId($authorIds);

        for ($i = 0; $i < 20; $i++) {
            for ($v = 0; $v < 50000; $v++) {
                $data[] = $factory->definition();
            }

            $chunks = array_chunk($data, 5000);
            $data = [];
            foreach ($chunks as $chunk) {
                Book::insert($chunk);
            }
        }
    }
}
