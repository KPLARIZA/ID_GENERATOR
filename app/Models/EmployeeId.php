<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeId extends Model
{
    protected $fillable = [
        'id_number',
        'first_name',
        'middle_initial',
        'last_name',
        'extension',
        'designation',
        'office_name',
        'profile_picture',
        'id_card_image',
        'qr_code_data',
        'signature',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->middle_initial}. {$this->last_name}";
    }
}
