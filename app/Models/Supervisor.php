<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'cnic',
        'selfie_path',
        'status',
        'rejection_reason',
    ];
protected $appends = [
    'selfie_secure_url',
];

public function getSelfieSecureUrlAttribute()
{
    return url("/api/admin/files/supervisor-selfie/{$this->id}");
}

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

