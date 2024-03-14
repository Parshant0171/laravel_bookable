<?php

namespace Jgu\Bookable\Observers;

use Jgu\Bookable\Models\BookableConfigurationSlot;
use Jgu\Bookable\Utils\DateUtils;


class BookableConfigurationSlotObserver
{

    public function saving(BookableConfigurationSlot $slot){
        if(DateUtils::isTimeInThePast($slot->getOriginal('start_time'))){
            return false;
        }
    }
}