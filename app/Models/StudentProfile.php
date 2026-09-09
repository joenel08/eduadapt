<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'lrn',
        'first_name',
        'middle_name',
        'last_name',
        'suffix_name',
        'birth_date',
        'sex',
        'mother_tongue',
        'ip_ethnic_group',
        'religion',
        'address_house',
        'address_barangay',
        'address_municipality',
        'address_province',
        'father_name',
        'mother_maiden_name',
        'guardian_name',
        'guardian_relationship',
        'contact_number',
        'learning_modality',
        'remarks',
        'contact_no',   
        'address',   
        'profile_picture',   
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function classRecords()
    {
        return $this->hasMany(StudentClassRecord::class);
    }
}
