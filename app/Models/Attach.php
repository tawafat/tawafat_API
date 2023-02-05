<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attach extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'url',
        'size',
        'folder',
        'note',
        'description',
        'created_by_id',
        'counter',
    ];

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function logs()
    {
        return $this->morphMany(DataLog::class, 'loggable');
    }
}
