<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    use HasFactory;
    protected $fillable = [
        'comment',
        'attachment_id',
        'attachmentType',
        'job_id',
        'created_by_id',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class ,'job_id');
    }
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
    public function attachment()
    {
        return $this->belongsTo(Attach::class, 'attachment_id');
    }
    public function logs()
    {
        return $this->morphMany(DataLog::class, 'loggable');
    }
}
