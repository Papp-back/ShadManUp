<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;

class Faq extends Model
{
    use HasFactory;
    protected $fillable = [
        'question',
        'answer',
    ];
}
