<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;

class AboutUs extends Model
{
    protected $table = 'about_us';

    protected $fillable = ['content'];
}
