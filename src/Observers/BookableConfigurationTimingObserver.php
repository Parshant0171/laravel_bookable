<?php

namespace Jgu\Bookable\Observers;

use Jgu\Bookable\Models\BookableConfigurationTiming;
use Jgu\Bookable\Utils\DateUtils;

class BookableConfigurationTimingObserver
{

    public function saving(BookableConfigurationTiming $config){
        $isOverNight = DateUtils::inOvernightRequest($config->start_time, $config->end_time);
        $config->start_time = DateUtils::$_DUMMY_DATES[DateUtils::$_DAYS_INDEX[$config->day_of_week]] . " " . $config->start_time;        
        if($isOverNight){
            $config->end_time = DateUtils::$_DUMMY_DATES[DateUtils::$_DAYS_INDEX[$config->day_of_week]+1] . " " . $config->end_time;
        }else{
            $config->end_time = DateUtils::$_DUMMY_DATES[DateUtils::$_DAYS_INDEX[$config->day_of_week]] . " " . $config->end_time;
        }
    }
}