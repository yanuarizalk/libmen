<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     @OA\Xml(
 *         name="Book"
 *     ))
 */
class Book extends Model
{
    protected $fillable = ['title', 'description', 'publish_date', 'author_id'];
    public static $orderable = ['id', 'title', 'publish_date'];

    /**
     * Book's title,
     * @var string
     * @OA\Property()
     */
    public ?string $title = null;

    /**
     * Book's description,
     * @var string
     * @OA\Property()
     */
    public ?string $description = null;

    /**
     * Published date,
     * @var \Date
     * @OA\Property()
     */
    public $publish_date;

    /**
     * Author's id,
     * @var int
     * @OA\Property()
     */
    public $author_id;

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}