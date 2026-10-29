<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['cover_message'];

    protected $casts = [
        'matched_skills' => 'array',
        'missing_skills' => 'array',
    ];

    public function jobPost() { return $this->belongsTo(JobPost::class); }
    public function applicant() { return $this->belongsTo(User::class, 'user_id'); }
}
