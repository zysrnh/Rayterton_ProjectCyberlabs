<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SeaService extends Model
{
    use HasUuids;

    protected $fillable = [
        'alumni_profile_id',
        'vessel_name',
        'vessel_type',
        'company_name',
        'rank_at_time',
        'start_date',
        'end_date',
        'duration_months',
        'route',
        'contract_file_url',
        'employer_confirmed',
        'verification_status'
    ];
}
