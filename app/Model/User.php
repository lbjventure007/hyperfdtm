<?php

declare(strict_types=1);

namespace App\Model;



use Hyperf\Scout\Searchable;

/**
 */
class User extends Model
{
    use Searchable;
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ["id","name","gender","balance"];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    public function searchableAs(): string
    {
        return "user_index";
    }

    public function toSearchableArray(): array
    {
        $arr = $this->toArray();

        return $arr;
    }
}
