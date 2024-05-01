<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;
use App\Models\CourseSection;
class CourseSession extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_section_id', 'title', 'description', 'file_path', 'file_name', 'file_type', 'file_size', 'duration_minutes','file_url'
    ];

    /**
     * Get the course section that owns the session.
     */
    public function courseSection()
    {
        return $this->belongsTo(CourseSection::class,'course_section_id');
    }
}