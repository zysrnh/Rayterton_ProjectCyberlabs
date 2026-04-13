<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AlumniProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'alumni_code',
        'full_name',
        'rank',
        'region',
        'phone',
        'avatar_url',
        'preferred_vessel_type',
        'preferred_route',
        'availability_status',
        'readiness_score',
        'sea_time_total_months',
        'profile_completeness',
        'verification_status',
        'verified_at',
        'membership_status',
        'linkedin_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function seaServices()
    {
        return $this->hasMany(SeaService::class);
    }
}
