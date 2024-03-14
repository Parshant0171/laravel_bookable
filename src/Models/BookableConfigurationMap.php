<?php

namespace Jgu\Bookable\Models;

class BookableConfigurationMap extends BookableBaseModel
{    
    public function configuration(){
        return $this->belongsTo(BookableConfiguration::class, 'bookable_configuration_id');
    }
}
