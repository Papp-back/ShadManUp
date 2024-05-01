<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;
use App\Models\User;
use App\Models\Course;
class CourseComment extends Model
{
    use HasFactory;
    protected $fillable = ['course_id', 'user_id', 'comment','show'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function likes()
    {
        return $this->belongsToMany(User::class, 'comment_likes')->select('users.id');
    }
    
    
   
}