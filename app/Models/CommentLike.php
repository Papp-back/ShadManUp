<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;
use App\Models\User;
class CommentLike extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'course_comment_id'];
}
