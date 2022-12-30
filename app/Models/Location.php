<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'country',
        'address',
        'long',
        'lat',
    ];

    public function job()
    {
        return $this->hasOne(Job::class);

    }


}
