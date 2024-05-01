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
 /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'show' => 'integer', // Cast the 'pay' column to integer
    ];
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