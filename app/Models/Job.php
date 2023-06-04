<?php

namespace App\Models;

use App\Enums\JobStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Job extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'radius',
        'status',
        'location_id',
        'category_slug',

        'assigned_to_id',
        'created_by_id',
        'updated_by_id',

        'enable_gps',
        'enable_studio',
        'type',
        ];

 /*   protected $casts = [
        'status' => JobStatusEnum::class
    ];*/
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_slug', 'slug');
    }

    public function assigned_to()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
    public function complains()
    {
        return $this->hasMany(Complain::class )->with('created_by');
    }

    public function logs()
    {
        return $this->morphMany(DataLog::class, 'loggable');
    }

    public function getEnableGpsAttribute($value)
    {
        return (bool) $value;
    }
    public function getEnableStudioAttribute($value)
    {
        //test 111
        return (bool) $value;
    }
}
