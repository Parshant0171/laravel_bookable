<?php

namespace Jgu\Bookable\Traits;

use Jgu\Bookable\Models\BookableRecurringBooking;

use Carbon\Carbon;
use Jgu\Bookable\Utils\DateUtils;
use Jgu\Bookable\Utils\ErrorUtils;

trait ReccursiveBookable {

    use Bookable;

    public function reccursiveBookings(){
        return $this->morphMany(BookableRecurringBooking::class, 'bookable');
    }

    public static function getAvailableReccursiveModels($models, $startDate, $endDate, $startTime, $endTime, $rMon=false, $rTue=false, $rWed=false, $rThu=false, $rFri=false, $rSat=false, $rSun=false){
        foreach($models as $model){
            $availability = $model->getModelReccursiveAvailability($startDate, $endDate, $startTime, $endTime, $rMon, $rTue, $rWed, $rThu, $rFri, $rSat, $rSun);
            $model->available = $availability;
        }
        return $models;
    }
    
    public function getModelReccursiveAvailability($startDate, $endDate, $startTime, $endTime, $rMon=false, $rTue=false, $rWed=false, $rThu=false, $rFri=false, $rSat=false, $rSun=false){
        // $carbonStart = Carbon::parse($startDate);
        // $carbonEnd = Carbon::parse($endDate);

        $carbonStart = $startDate;
        $carbonEnd = $endDate;

        $overNight=DateUtils::inOvernightRequest($startTime, $endTime);

        $period = DateUtils::getCarbonPeriodForReccursiveBookings($carbonStart, $carbonEnd, $rMon, $rTue, $rWed, $rThu, $rFri, $rSat, $rSun);

        if(count($period)>0){
            foreach($period as $date){
                $start = $date->format('Y-m-d') . " " . $startTime;
                $end = $overNight ? $date->addDay()->format('Y-m-d') . $endTime : $date->format('Y-m-d') . " " . $endTime;
                if (!self::getAvailableModels($start, $end, $this)){
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function createReccursiveBooking($customer, $startDate, $endDate, $startTime, $endTime, $seats = 1, $rMon=false, $rTue=false, $rWed=false, $rThu=false, $rFri=false, $rSat=false, $rSun=false){
        if(!$this->getModelReccursiveAvailability($startDate, $endDate, $startTime, $endTime, $rMon, $rTue, $rWed, $rThu, $rFri, $rSat, $rSun)){
            return ErrorUtils::renderCreateError('MODEL_UNAVAILABLE');
        }        

        $booking = BookableRecurringBooking::create([
            'bookable_id' => $this->id,
            'bookable_type' => $this->bookableGetClassName(),
            'customer_id' => $customer->id,
            'customer_type' => $customer->bookableGetClassName(),
            'starts' => $startDate,
            'ends' => $endDate,
            'recurring_start_time' => $startTime,
            'recurring_end_time' => $endTime,
            'reccurs_monday' => $rMon ? 1 : 0,
            'reccurs_tuesday' => $rTue ? 1 : 0,
            'reccurs_wednesday' => $rWed ? 1 : 0,
            'reccurs_thursday' => $rThu ? 1 : 0,
            'reccurs_friday' => $rFri ? 1 : 0,
            'reccurs_saturday' => $rSat ? 1 : 0,
            'reccurs_sunday' => $rSun ? 1 : 0,
        ]);

        // $carbonStart = Carbon::parse($startDate);
        // $carbonEnd = Carbon::parse($endDate);

        $carbonStart = $startDate;
        $carbonEnd = $endDate;

        $overNight=DateUtils::inOvernightRequest($startTime, $endTime);
        
        $period = DateUtils::getCarbonPeriodForReccursiveBookings($carbonStart, $carbonEnd, $rMon, $rTue, $rWed, $rThu, $rFri, $rSat, $rSun);          

        foreach($period as $date){
            $start = $date->format('Y-m-d') . " " . $startTime;
            $end = $overNight ? $date->addDay()->format('Y-m-d') . " " . $endTime : $date->format('Y-m-d') . " " . $endTime;
            $b = $this->createNewBooking($customer, $start, $end, $seats, $booking->id);
            
            if($b->error && $b->error === true){
                $booking->cancelBooking($customer);
                return $b;
            }
        }
        return $booking;
    }
}