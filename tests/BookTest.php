<?php

namespace Tests;
use Carbon\Carbon;
use Database\Factories\AuthorFactory;

class BookTest extends TestCase
{
    protected static $initialized = false;
    protected static $now;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$initialized) {
            $this->artisan('migrate:fresh');

            self::$now = Carbon::now()->setMillis(0);
            Carbon::setTestNow(self::$now);

            self::$initialized = true;
        }
    }

    public function testCreateBook()
    {
        AuthorFactory::new()->create([
            'name' => 'Ben Owalski',
        ]);

        $data = [
            'title' => 'Pride and Prejudice',
            'description' => 'A romantic novel of manners written by Jane Austen.',
            'publish_date' => '1813-01-28',
            'author_id' => 1,
        ];

        $this->post('/book', $data, []);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'id',
            'title',
            'description',
            'publish_date',
            'author_id',
            'created_at',
            'updated_at'
        ]);
    }

    /**
     * @depends testUpdateBook
     */
    public function testGetSingleBook()
    {
        $this->get('/book/1', []);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'id',
            'title',
            'description',
            'publish_date',
            'author_id',
            'created_at',
            'updated_at'
        ]);
    }

    /**
     * @depends testCreateBook
     */
    public function testUpdateBook()
    {
        $data = [
            'title' => 'Pride and Prejudice Updated',
            'description' => 'An updated description of the romantic novel.',
            'publish_date' => '1813-01-28',
            'author_id' => 1,
        ];

        $this->put('/book/1', $data, []);
        $this->seeStatusCode(200);
        $this->seeJsonEquals([
            'id' => 1,
            'title' => 'Pride and Prejudice Updated',
            'description' => 'An updated description of the romantic novel.',
            'publish_date' => '1813-01-28',
            'author_id' => 1,
            'created_at' => self::$now,
            'updated_at' => self::$now
        ]);
    }

    /**
     * @depends testUpdateBook
     */
    public function testGetAllBooks()
    {
        $this->get('/book', []);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'current_page',
            'to',
            'total',
            'per_page',
            'data' => ['*' => ['id', 'title', 'description', 'publish_date', 'author_id', 'created_at', 'updated_at']]
        ]);
    }

    public function testGetNotFoundForNonExistentBook()
    {
        $this->get('/book/999', []);
        $this->seeStatusCode(404);
        $this->seeJsonEquals([
            'message' => 'data not found'
        ]);
    }

    /**
     * @depends testGetAllBooks
     */
    public function testDeleteBook()
    {
        $this->delete('/book/1', [], []);
        $this->seeStatusCode(204);
    }
}