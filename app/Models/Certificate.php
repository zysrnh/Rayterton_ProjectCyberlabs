<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Certificate extends Model
{
    use HasUuids;

    protected $fillable = [
        'alumni_profile_id',
        'cert_name',
        'cert_number',
        'issuing_body',
        'issued_date',
        'expiry_date',
        'is_expiring_soon',
        'cert_file_url',
        'verification_status',
        'endorsements'
    ];

    public function alumniProfile()
    {
        return $this->belongsTo(AlumniProfile::class);
    }
}
