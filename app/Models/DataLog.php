<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLog extends Model
{
    use HasFactory;
    protected $hidden = [
        'data','loggable_type', 'loggable_id'
    ];
    protected $fillable = [
        'action',
        'type',
        'data',
        'created_by_id',
        'predicate_table_name',
        'predicate_id',
    ];

    public function notification_log()
    {
        return $this->hasMany(NotificationLog::class, 'log_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class);
    }

    public function loggable()
    {
        return $this->morphTo();
    }
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
