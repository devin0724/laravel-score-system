<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'student_name',
        'parent_phone',
        'scores',
    ];

    protected $casts = [
        'scores' => 'array',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}