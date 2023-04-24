<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'course_id'
    ];

    public function course(){
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons(){
        return $this->hasMany(Lesson::class, 'chapter_id');
    }
}
