<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id',
        'name',
        'level',
    ];
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}