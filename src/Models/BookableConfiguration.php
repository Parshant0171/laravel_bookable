<?php

namespace Jgu\Bookable\Models;

use Illuminate\Database\Eloquent\Builder;

class BookableConfiguration extends BookableBaseModel
{

    // protected static function booted()
    // {
    //     static::addGlobalScope('allow_bookings', function (Builder $builder) {
    //         $builder->where('allow_bookings', 1);
    //     });
    // }    
    
    public function timings(){
        return $this->hasMany(BookableConfigurationTiming::class);
    }

    public function slots(){
        return $this->hasMany(BookableConfigurationSlot::class);
    }

    public function maps(){
        return $this->hasMany(BookableConfigurationMap::class);
    }
    
}
