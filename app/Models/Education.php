<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Education extends Model
{
    use HasUuids;

    protected $table = 'educations';

    protected $fillable = [
        'alumni_profile_id',
        'institution_name',
        'degree_program',
        'graduation_year',
        'diploma_file_url',
        'is_verified'
    ];
}
