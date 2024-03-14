<?php

namespace Jgu\Bookable\Models;

class BookableConfigurationTiming extends BookableBaseModel
{    

    public function configuration(){
        return $this->belongsTo(BookableConfiguration::class);
    }
}
