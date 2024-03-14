<?php

namespace Jgu\Bookable\Models;

use Jgu\Bookable\Traits\RecursiveCancelableBooking;
use Illuminate\Database\Eloquent\Builder;

class BookableRecurringBooking extends BookableBaseModel
{    
    use RecursiveCancelableBooking;

    protected static function booted()
    {
        static::addGlobalScope('not_cancelled', function (Builder $builder) {
            $builder->whereNull('cancelled_at');
        });
    }

    public function getBookableConfiguration(){                
        $this->load('bookable.configurationMap.configuration');
        if($this->bookable->configurationMap && $this->bookable->configurationMap->configuration){
            return $this->bookable->configurationMap->configuration;
        }        
        return null;
    }
    
    public function bookable(){
        return $this->morphTo();
    }

    public function customer(){
        return $this->morphTo();
    }

    protected $guarded = [];
}
