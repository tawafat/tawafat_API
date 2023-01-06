<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
    ];

    public const IS_ADMIN = 1;
    public const IS_MANAGER = 2;
    public const IS_EMPLOYEE = 3;





    public function user()
    {
        return $this->hasMany(User::class );
    }
}
