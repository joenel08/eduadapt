<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'login_id',
        'password',
        'role',
        'disabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'disabled' => 'boolean',
    ];

    // Relationships
    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }
  

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    // Helper to get the appropriate profile
    public function getProfileAttribute()
    {
        return match ($this->role) {
            'admin'   => $this->adminProfile,
            'teacher' => $this->teacherProfile,
            'student' => $this->studentProfile,
        };
    }

    // Helper to get full name from profile
    public function getFullNameAttribute()
    {
        $profile = $this->profile;
        if (!$profile) return $this->login_id;

        return match ($this->role) {
            'admin'   => $profile->full_name,
            'teacher' => trim(implode(' ', array_filter([$profile->prefix_name, $profile->first_name, $profile->middle_name, $profile->last_name, $profile->suffix_name]))),
            'student' => trim(implode(' ', array_filter([$profile->first_name, $profile->middle_name, $profile->last_name, $profile->suffix_name]))),
        };
    }
}