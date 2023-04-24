<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'title', 
        'description', 
        'thumbnail', 
        'level', 
        'price', 
        'category_id',
        'hours',
        'weeks',
        'creator_name',
        'creator_role',
        'creator_bio'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');    
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'course_id');
    }

    public function lessons(){
        return $this->hasManyThrough(Lesson::class, Chapter::class, 'course_id', 'chapter_id');
    }
}
