<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class JobPost extends Model
{
     use HasFactory;
    protected $fillable = [
        'title', 'company_name', 'description', 'location',
        'type', 'salary_min', 'salary_max', 'status',
    ];

    public function employer() { return $this->belongsTo(User::class, 'user_id'); }
    public function applications() { return $this->hasMany(Application::class); }
}
