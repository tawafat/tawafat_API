<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'to_user',
        'type',
        'message',
        'notification_sent',
        'notification_read',
        'email_sent',
        'log_id'
    ];

    public function dataLog()
    {
        return $this->belongsTo(DataLog::class, 'log_id');
    }
    public function to_user_data()
    {
        return $this->belongsTo(User::class, 'to_user');
    }
}
