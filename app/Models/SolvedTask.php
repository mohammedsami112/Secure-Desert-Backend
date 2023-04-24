<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolvedTask extends Model
{
    use HasFactory;

    protected $table = 'solved_tasks';

    protected $fillable = [
        'task_id', 'user_id', 'attachment', 'content',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
