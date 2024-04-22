<?php


// app/Models/BaseModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\JalaliDateTrait;

class BaseModel extends Model
{
    use JalaliDateTrait;

    public function withJdate()
    {
       
        if ($this->created_at) {
            $this->jcreated_at = $this->getJCreatedAtAttribute();
        }
        if ($this->updated_at) {
            $this->jupdated_at = $this->getJUpdatedAtAttribute();
        }
        
       

        return $this;
    }
    public function withJdateHuman()
    {
        $this->jcreated_at = $this->getJCreatedAtAttribute();
        $this->jupdated_at = $this->getJUpdatedAtAttribute();
        $this->created_at_for_humans = $this->getCreatedAtForHumansAttribute();
        $this->updated_at_for_humans = $this->getUpdatedAtForHumansAttribute();
        // $this->deleted_at_for_humans = $this->getDeletedAtForHumansAttribute();
       

        return $this;
    }
    
    public function prettifyPrice()
    {

        $this->price_prettified=number_format(floatval($this->price), 0, '.', ',');
        $this->discounted_prettified=number_format(floatval($this->discount), 0, '.', ',');
        $this->final_price_prettified=number_format(floatval($this->price)-floatval($this->discount), 0, '.', ',');
        return $this;
    }
}