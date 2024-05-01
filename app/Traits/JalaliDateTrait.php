<?php

// app/Traits/JalaliDateTrait.php

namespace App\Traits;

use App\Helpers\JalaliHelper;

trait JalaliDateTrait
{
    public function getJCreatedAtAttribute()
    {
        return $this->attributes['created_at']?JalaliHelper::convertToJalali($this->attributes['created_at']):null;
    }

    public function getJUpdatedAtAttribute()
    {
        return $this->attributes['updated_at']?JalaliHelper::convertToJalali($this->attributes['updated_at']):null;
    }
    public function getJBornAtAttribute()
    {
        return $this->attributes['born_at']?JalaliHelper::convertToJalali2($this->attributes['born_at']):null;
    }

    public function getCreatedAtForHumansAttribute()
    {
        return $this->formatJalaliDiffForHumans($this->created_at);
    }

    public function getUpdatedAtForHumansAttribute()
    {
        return $this->formatJalaliDiffForHumans($this->updated_at);
    }

    public function getDeletedAtForHumansAttribute()
    {
        return $this->formatJalaliDiffForHumans($this->deleted_at);
    }

    protected function formatJalaliDiffForHumans($date)
    {
        if (!$date) {
           return null;
        }
        $diff = $date->diffInMinutes();

        if ($diff < 1) {
            return 'چند لحظه پیش';
        } elseif ($diff < 60) {
            return JalaliHelper::convertToJalali($diff) . ' دقیقه پیش';
        } elseif ($diff < 1440) {
            return $date->diffInHours() . ' ساعت پیش';
        } elseif ($diff < 10080) {
            return $date->diffInDays() . ' روز پیش';
        } elseif ($diff < 43200) {
            return $date->diffInWeeks() . ' هفته پیش';
        }
        return JalaliHelper::convertToJalali($date->diffForHumans());
    }
}