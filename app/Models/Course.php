<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;
use App\Models\CourseSection;
use App\Models\CourseComment;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'category_id',
        'author',
        'description',
        // 'price',
        // 'discount',
        'summary',
        'image',
    ];

    // Define the relationship with the Category model
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
       /**
     * Get the course that owns the session.
     */
    public function sections()
    {
        return $this->hasMany(CourseSection::class)->with('sessions')->withCount('sessions');
    }
    public function comments()
    {
        return $this->hasMany(CourseComment::class)->with('user')->withCount('likes');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'course_id');
    }
}