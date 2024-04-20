<?php

// app/Traits/StatusInfoTrait.php

namespace App\Traits;



trait BlogStatusInfoTrait
{
    public function getStatusInfoAttribute()
    {
       
        if (!isset($this->attributes['status'])) {
            return null;
        }
        switch ($this->attributes['status']) {
            case 'PUBLISHED':
                return [
                    "name"=> "منتشرشده",
                    "color"=> "success"
                ];
            case 'DRAFT':
                return [
                    "name"=> "پیش نویس",
                    "color"=> "yellow"
                ];
            case 'DISABLED':
                return [
                    "name"=> "غیر غعال",
                    "color"=> "red"
                ];
            case 'PUBLISHING':
                return [
                    "name"=> "در حال انتشار",
                    "color"=> "success"
                ];
            default:
                return [
                    "name"=> "پیش نویس",
                    "color"=> "yellow"
                ];
        }
    }

   
}