<?php

// app/Traits/StatusInfoTrait.php

namespace App\Traits;



trait TypeInfoTrait
{
    public function getTypeInfoAttribute()
    {
       
        if (!isset($this->attributes['type'])) {
            return null;
        }
        switch ($this->attributes['type']) {
            case 'TEXT':
                return [
                    "name"=> "متنی",
                ];
            case 'BOOLEAN':
                return [
                    "name"=> "تک حالتی",
                ];
            case 'SINGLE_OPTION':
                return [
                    "name"=> "انتخاب تکی",
                ];
            case 'MULTIPLE_OPTION':
                return [
                    "name"=> "انتخاب دسته ای",
                ];
            default:
                return 'Unknown';
        }
    }

   
}