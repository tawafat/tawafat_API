<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobDetail extends Model
{
    use HasFactory;

    protected $table = 'job_details';

    protected $fillable = [
        'job_type',
        'no_of_packages',
        'rejected_packages',
        'min_weight',
        'date_time',
        'gate_number',
        'no_entering',
        'no_exiting',
        'no_inside',
        'camp_number',
        'temperature',
        'humidity',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
