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
        'status',
        'location_id',
        'category_slug',

        'assigned_to_id',
        'created_by_id',
        'updated_by_id',
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
}
