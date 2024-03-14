<?php

namespace Jgu\Bookable\Models;

class BookableConfigurationSlot extends BookableBaseModel
{    
    public function configuration(){
        return $this->belongsTo(BookableConfiguration::class, 'bookable_configuration_id');
    }  
    
    public function bookings(){
        return $this->hasMany(BookableBooking::class);
    }
}
