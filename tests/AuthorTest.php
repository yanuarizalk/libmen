<?php

namespace Tests;
use Carbon\Carbon;

class AuthorTest extends TestCase
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

    public function testCreateAuthor()
    {
        $data = [
            'name' => 'Jane Austen',
            'bio' => 'English novelist known for her six major novels.',
            'birth_date' => '1775-12-16',
        ];

        $this->post('/author', $data, []);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'id',
            'name',
            'bio',
            'birth_date',
            'created_at',
            'updated_at'
        ]);
        var_dump($this->response->getContent());
    }

    /**
     * @depends testCreateAuthor
     */
    public function testUpdateAuthor()
    {
        $data = [
            'name' => 'Jane Austen Updated',
            'bio' => 'Updated English novelist bio.',
            'birth_date' => '1775-12-16',
        ];

        $this->put('/author/1', $data, ['Accept' => 'application/json']);
        print_r($this->response->getContent());
        $this->seeStatusCode(200);
        $this->seeJsonEquals([
            'id' => 1,
            'name' => 'Jane Austen Updated',
            'bio' => 'Updated English novelist bio.',
            'birth_date' => '1775-12-16',
            'created_at' => self::$now,
            'updated_at' => self::$now,
        ]);
    }

    /**
     * @depends testUpdateAuthor
     */
    public function testGetSingleAuthor()
    {
        $this->get('/author/1', []);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'id',
            'name',
            'bio',
            'birth_date',
            'created_at',
            'updated_at'
        ]);
    }

    public function testGetNotFoundForNonExistentAuthor()
    {
        $this->get('/author/999', []);
        $this->seeStatusCode(404);
        $this->seeJsonEquals([
            'message' => 'data not found'
        ]);
    }

    /**
     * @depends testUpdateAuthor
     */
    public function testGetAllBooksByAuthor()
    {
        $this->get('/author/1/books', []);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'current_page',
            'to',
            'total',
            'per_page',
            'data' => ['*' => ['id', 'title', 'description', 'publish_date', 'author_id', 'created_at', 'updated_at']],
        ]);
    }

    /**
     * @depends testGetAllBooksByAuthor
     */
    public function testDeleteAuthor()
    {
        $this->delete('/author/1', [], []);
        $this->seeStatusCode(204);
    }

    public function testDeleteAuthorNotFound()
    {
        $this->delete('/author/828218', [], []);
        $this->seeStatusCode(404);
    }
}