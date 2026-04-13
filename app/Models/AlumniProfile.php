<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AlumniProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'alumni_id',
        'full_name',
        'rank',
        'readiness_score',
        'sea_time_total',
        'availability_status',
        'profile_completeness',
        'preferred_route',
        'preferred_vessel',
        'phone',
        'region',
        'open_to_offers',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
