<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;
use App\Models\Course;
use App\Models\CourseSession;
class CourseSection extends Model
{
    use HasFactory;


     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id', 'title', 'description','price','discount'
    ];

    /**
     * Get the course that owns the session.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function sessions()
    {
        return $this->hasMany(CourseSession::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class, 'section_id');
    }
}