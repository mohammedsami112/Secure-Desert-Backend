<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cart_id',
        'cart_description',
        'user_id',
        'course_id',
        'plan_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that owns the cart.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the plan that owns the cart.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
