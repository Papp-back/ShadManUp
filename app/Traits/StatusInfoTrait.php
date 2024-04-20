<?php

// app/Traits/StatusInfoTrait.php

namespace App\Traits;



trait StatusInfoTrait
{
    public function getStatusInfoAttribute()
    {
       
        if (!isset($this->attributes['status'])) {
            return null;
        }
        switch ($this->attributes['status']) {
            case 'ACTIVE':
                return [
                    "name"=> "فعال",
                    "color"=> "success"
                ];
            case 'INACTIVE':
                return [
                    "name"=> "غیر فعال",
                    "color"=> "red"
                ];
            // Add more cases if needed
            default:
                return 'Unknown';
        }
    }

   
}