<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     @OA\Xml(
 *         name="Author"
 *     ))
 */
class Author extends Model
{
    protected $fillable = ['name', 'bio', 'birth_date'];
    public static $orderable = ['id', 'name', 'birth_date'];

    /**
     * Author's name,
     * @var string
     * @OA\Property()
     */
    public string $name;

    /**
     * Author's biography,
     * @var string
     * @OA\Property()
     */
    public string $bio;

    /**
     * Author's birth date,
     * @var string
     * @OA\Property(
     *  type="date"
     * )
     */
    public $birth_date;

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}