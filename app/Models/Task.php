<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'points',
        'answer',
        'link',
        'level',
        'description',
        'wirte_up_title',
        'wirte_up_content',
        'wirte_up_thumbnail',
        'attachment',
        'category',
    ];

    public function taskCategory()
    {
        return $this->belongsTo(Category::class, 'category');
    }
}
