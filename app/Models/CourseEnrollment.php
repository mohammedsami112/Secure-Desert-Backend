<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $table = 'courses_enrollments';

    protected $fillable = [
        'user_id', 'course_id', 'progress',
    ];
}
