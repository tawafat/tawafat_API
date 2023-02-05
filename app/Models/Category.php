<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'name',
        'description'
    ];


    public function jobs()
    {
        return $this->hasMany(Job::class, 'category_slug', 'slug');
    }
    public function logs()
    {
        return $this->morphMany(DataLog::class, 'loggable');
    }
}
