<?php

namespace Jgu\Bookable\Utils;

use Carbon\Carbon;

class DateUtils {

    public static $_DAYS = [
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ];
    
    public static $_DAYS_INDEX = [
        "Sunday" => 0, "Monday" => 1, "Tuesday" => 2, "Wednesday" => 3, "Thursday" => 4, "Friday" => 5, "Saturday" => 6
    ];

    public static $_DUMMY_DATES = [
        "2021-11-14", "2021-11-15", "2021-11-16", "2021-11-17", "2021-11-18", "2021-11-19", "2021-11-20"
    ];

    public static function getCarbonPeriodForReccursiveBookings($carbonStart, $carbonEnd, $rMon=false, $rTue=false, $rWed=false, $rThu=false, $rFri=false, $rSat=false, $rSun=false){
        $period = [];
        if($rMon)
            $period = array_merge($period, DateUtils::getCarbonPeriod($carbonStart, $carbonEnd, "Monday"));
        if($rTue)
            $period = array_merge($period, DateUtils::getCarbonPeriod($carbonStart, $carbonEnd, "Tuesday"));
        if($rWed)
            $period = array_merge($period, DateUtils::getCarbonPeriod($carbonStart, $carbonEnd, "Wednesday"));
        if($rThu)
            $period = array_merge($period, DateUtils::getCarbonPeriod($carbonStart, $carbonEnd, "Thursday"));
        if($rFri)
            $period = array_merge($period, DateUtils::getCarbonPeriod($carbonStart, $carbonEnd, "Friday"));
        if($rSat)
            $period = array_merge($period, DateUtils::getCarbonPeriod($carbonStart, $carbonEnd, "Saturday"));
        if($rSun)
            $period = array_merge($period, DateUtils::getCarbonPeriod($carbonStart, $carbonEnd, "Sunday"));   
        return $period;     
    }

    public static function getCarbonPeriod($startDate, $endDate, $day){

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $startDay = $startDate->dayOfWeek;
        $dayIndex = self::$_DAYS_INDEX[$day];

        $diffInDays = $dayIndex - $startDay;
        if ($diffInDays<0)
            $diffInDays+=7;

        $actualStartDate = $startDate->addDay($diffInDays);

        return $actualStartDate->daysUntil($endDate, 7)->toArray();
    }

    public static function isTimeInThePast($time){
        $carbonTime = Carbon::parse($time);
        $now = Carbon::now();
        if($now > $carbonTime){
            return true;
        }
        return false;
    }

    public static function getDifferenceInMinutes($time){
        $carbonTime = Carbon::parse($time);
        $now = Carbon::now();
        return $carbonTime->diffInMinutes($now);
    }

    public static function inOvernightRequest($startTime, $endTime){
        $carbonStart = Carbon::parse("2021-11-13 " . $startTime);
        $carbonEnd = Carbon::parse("2021-11-13 " . $endTime);        
        if($carbonStart > $carbonEnd)
            return true;
        return false;
    }

    public static function getMysqlQueryStringWithDummyDates($carbonDate){
        return DateUtils::$_DUMMY_DATES[$carbonDate->dayOfWeek] . " " . $carbonDate->toTimeString();
    }

    public static function getDaysBetween($fromDate, $toDate, $nonInclusive=false){
        $carbon = new Carbon();
        if($fromDate->diffInDays($toDate)>=7){
            return DateUtils::$_DAYS;
        }
        $from = $fromDate->dayOfWeek;
        $to = $toDate->dayOfWeek;        
        
        if($to<$from) $to+=7;

        if($nonInclusive){
            $from++;$to--;
        }

        $days = [];
        for($i = $from;$i<=$to;$i++){
            $days[] = DateUtils::$_DAYS[$i%7];    
        }
        return $days;
    }

    public static function getMySQLDateString($carbonDate){
        return $carbonDate->year . "-" . $carbonDate->month . "-" . $carbonDate->day;
    }

    public static function getMySQLTimeString($carbonDate){
        return $carbonDate->hour . ":" . $carbonDate->minute . ":" . $carbonDate->second;
    }

    public static function getMySQLDateTimeString($carbonDate){
        return DateUtils::getMySQLDateString($carbonDate) . " " . DateUtils::getMySQLDateTimeString($carbonDate);
    }    

}