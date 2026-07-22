<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_name',
        'exam_code',
        'subjects',
        'total_students',
        'expires_at',
    ];

    protected $casts = [
        'subjects' => 'array',
        'expires_at' => 'datetime',
    ];

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function queryLogs()
    {
        return $this->hasMany(QueryLog::class);
    }

    public function smsCodes()
    {
        return $this->hasMany(SmsCode::class);
    }
}