<?php

namespace Jgu\Bookable\Traits;

use Jgu\Bookable\Models\BookableBooking;
use Jgu\Bookable\Models\BookableConfiguration;
use Jgu\Bookable\Models\BookableConfigurationMap;

use Jgu\Bookable\Utils\Utils;
use Jgu\Bookable\Utils\DateUtils;
use Jgu\Bookable\Utils\ErrorUtils;

use Carbon\Carbon;

trait Bookable {   
    
    use BookableBase;

    public function bookableGetClassName(){
        return $this->className ?? get_class();
    }

    public function getTraitID(){
        return 'model';
    }

    public function dumps($data){
        echo json_encode($data);
    }

    public function bookings(){
        return $this->morphMany(BookableBooking::class, 'bookable');
    }

    public function configurationMap(){
        return $this->morphOne(BookableConfigurationMap::class, 'mappable');
    }

    /*
        get availability for this model between timestamps
    */
    public function getModelAvailability($fromDateTime, $toDateTime){        
        return self::getAvailableModels($fromDateTime, $toDateTime, $this);
    }

    /*
        get availability for all models between timestamps
    */
    public static function getAvailableModels($fromDateTime, $toDateTime, $models = null){
        $carbon = new Carbon();
        $carbonFrom = $carbon->createFromDate($fromDateTime);
        $carbonTo = $carbon->createFromDate($toDateTime);                
        
        $daysBetween = DateUtils::getDaysBetween($carbonFrom, $carbonTo);
                
        $overNightBooking = sizeof($daysBetween)===2;
        $multipleDays = sizeof($daysBetween)>2;

        
        if($models == null) {
            $models = self::all();
        }        
        if($overNightBooking){
            $models->load([
                'configurationMap.configuration.timings' => function ($query) use ($daysBetween, $carbonFrom, $carbonTo) {
                    $query->where('day_of_week', $daysBetween[0])
                            ->where("start_time", "<=", DateUtils::getMysqlQueryStringWithDummyDates($carbonFrom))
                            ->where("end_time", ">=", DateUtils::getMysqlQueryStringWithDummyDates($carbonTo));
                }, 
                'bookings' => function ($query) use ($fromDateTime, $toDateTime) {
                    $query->whereRaw('(("'.$fromDateTime.'" between `bookable_bookings`.`starts` and `bookable_bookings`.`ends` 
                                        or "'.$toDateTime.'" between `bookable_bookings`.`starts` and `bookable_bookings`.`ends` )
                                        or (`bookable_bookings`.starts between "'.$fromDateTime.'" and "'.$toDateTime.'"
                                        or `bookable_bookings`.ends between "'.$fromDateTime.'" and "'.$toDateTime.'"))');
                }
                ]);
        }else if ($multipleDays){   
            $models->load([
                'configurationMap.configuration'
            ]);
            $config = null;
            
            if(get_class($models) == "Illuminate\Database\Eloquent\Collection"){
                if($models[0] && $models[0]->configurationMap)
                    $config = $models[0]->configurationMap->configuration ?? null;
            }else{
                if($models && $models->configurationMap)
                    $config = $models->configurationMap->configuration ?? null;
            }
            if($config == null)
                return [];                     
            if($config->completely_open_ended==1){
                $models->load([
                    'bookings' => function ($query) use ($fromDateTime, $toDateTime) {
                        $query->whereRaw('(("'.$fromDateTime.'" between `bookable_bookings`.`starts` and `bookable_bookings`.`ends` 
                                        or "'.$toDateTime.'" between `bookable_bookings`.`starts` and `bookable_bookings`.`ends` )
                                        or (`bookable_bookings`.starts between "'.$fromDateTime.'" and "'.$toDateTime.'"
                                        or `bookable_bookings`.ends between "'.$fromDateTime.'" and "'.$toDateTime.'"))');
                    }    
                ]);

            }else{
                return [];
            }                        
        } else{
            $models->load([
                'configurationMap.configuration.timings' => function ($query) use ($daysBetween, $carbonFrom, $carbonTo) {
                    $query->whereIn('day_of_week', $daysBetween)
                            ->where("start_time", "<=", DateUtils::getMysqlQueryStringWithDummyDates($carbonFrom))
                            ->where("end_time", ">=", DateUtils::getMysqlQueryStringWithDummyDates($carbonTo));
                }, 
                'bookings' => function ($query) use ($fromDateTime, $toDateTime) {

                    $query->whereRaw('(("'.$fromDateTime.'" between `bookable_bookings`.`starts` and `bookable_bookings`.`ends` 
                                        or "'.$toDateTime.'" between `bookable_bookings`.`starts` and `bookable_bookings`.`ends` )
                                        or (`bookable_bookings`.starts between "'.$fromDateTime.'" and "'.$toDateTime.'"
                                        or `bookable_bookings`.ends between "'.$fromDateTime.'" and "'.$toDateTime.'"))');

                    // $query->whereBetween('starts', [$carbonFrom, $carbonTo])
                            // ->orWhereBetween('ends', [$carbonFrom, $carbonTo]);
                }
                ]);                
        }    
        
        
        if(get_class($models) == "Illuminate\Database\Eloquent\Collection"){
            $availability = [];
            foreach($models as $model){
                if($model->configurationMap){
                    $a = false;
                    if($model->configurationMap->configuration->uses_approvals==1){                    
                        $bookingsSize = 0;
                        if($model->bookings && sizeof($model->bookings)>0){
                            foreach($model->bookings as $booking){
                                if($booking->is_finally_approved)
                                    $bookingsSize+=$booking->no_of_seats;
                            }
                        }
                    }else{
                        $bookingsSize = $model->bookings ? sizeof($model->bookings) : 0;
                    }   
                                                
                    if($model->configurationMap->configuration->allow_seatwise_booking==1){
                        $key = self::getSeatsKey();
                        if($model->configurationMap->configuration->uses_approvals==0){
                            $totalSeatsBooked = 0;                        
                            if($model->bookings && sizeof($model->bookings)>0){
                                foreach($model->bookings as $booking){                                
                                    $totalSeatsBooked+=$booking->no_of_seats;
                                }
                            }
                        }else{
                            $totalSeatsBooked = $bookingsSize;
                        }                    
                        if($model->configurationMap && $model->configurationMap->configuration && $model->configurationMap->configuration->timings && sizeof($model->configurationMap->configuration->timings)>0 && $totalSeatsBooked<$model[$key]){
                            $a = true;          
                            $model->seats_available = $model[$key] - $totalSeatsBooked;              
                        }
                    }
                    else if($multipleDays && $bookingsSize==0)
                        $a = true;
                    else if($model->configurationMap && $model->configurationMap->configuration && $model->configurationMap->configuration->timings && sizeof($model->configurationMap->configuration->timings)>0 && $bookingsSize==0){
                        $a = true;
                    }

                    if($model->configurationMap->configuration->open_booking_before_start_time_minutes!=null){
                        if(DateUtils::getDifferenceInMinutes($fromDateTime) > $model->configurationMap->configuration->open_booking_before_start_time_minutes){
                            $a = false;                        
                        }
                    }

                    unset($model->configurationMap);
                    unset($model->bookings);
        
                    $model->available = $a;
                    $availability[] = $model;
                }
            }
    
            return $availability;
        } else {
            if($models->configurationMap){  
                if($models->configurationMap->configuration->uses_approvals==1){
                    $bookingsSize = 0;
                    if($models->bookings && sizeof($models->bookings)>0){
                        foreach($models->bookings as $booking){
                            if($booking->is_finally_approved)
                                $bookingsSize+=$booking->no_of_seats;
                        }
                    }
                }else{
                    $bookingsSize = $models->bookings ? sizeof($models->bookings) : 0;
                }          
                if($models->configurationMap->configuration->allow_seatwise_booking==1){
                    if($models->configurationMap->configuration->uses_approvals==0){
                        $totalSeatsBooked = 0;
                        if($models->bookings && sizeof($models->bookings)>0){
                            foreach($models->bookings as $booking){                            
                                $totalSeatsBooked+=$booking->no_of_seats;
                            }
                        }
                    }else{
                        $totalSeatsBooked = $bookingsSize;
                    }
                    $key = self::getSeatsKey();
                    if($models->configurationMap && $models->configurationMap->configuration && $models->configurationMap->configuration->timings && sizeof($models->configurationMap->configuration->timings)>0 && $totalSeatsBooked<$models[$key]){
                        return true;
                    }
                }
                else if($multipleDays && $bookingsSize==0)
                    return true;
                else if($models->configurationMap && $models->configurationMap->configuration && $models->configurationMap->configuration->timings && sizeof($models->configurationMap->configuration->timings)>0 && $bookingsSize==0){
                    return true;
                }
                
                if($models->configurationMap->configuration->open_booking_before_start_time_minutes!=null){
                    if(DateUtils::getDifferenceInMinutes($fromDateTime) > $models->configurationMap->configuration->open_booking_before_start_time_minutes){
                        $a = false;                        
                    }
                }
            }

            return false;
        }        
    }    


    private function validateOngoingBookings($allowed, $customer){        
        $this->load([
            'configurationMap.configuration'
        ]);
        $config = $this->configurationMap->configuration;
        $query = BookableBooking::where('bookable_type', $this->bookableGetClassName())
                    ->where('customer_id', $customer->id)
                    ->where('customer_type', $customer->bookableGetClassName())
                    ->where('starts', '>=', Carbon::now());
        if($config->is_form_builder){
            $query->where('bookable_id',$this->id);
        }
        $userBookings = $query->get();
        if($userBookings->count()>=$allowed){
            return ErrorUtils::renderCreateError('MAX_BOOKINGS', 
                ['ongoing_bookings' => $userBookings->count(),
                'max_bookings' => $allowed]);
        }
        return true;
    }
    
    private function validateWithConfiguration($customer, $starts, $ends, $seats){
        $carbon = new Carbon();
        $carbonStarts = $carbon->createFromDate($starts);
        $carbonTo = $carbon->createFromDate($ends);
        $config = null;
        $this->load([
            'configurationMap.configuration'
        ]);
        if($this->configurationMap)
            $config = $this->configurationMap->configuration ?? null;

        if($config == null)
            return ErrorUtils::renderCreateError('INCORRECT_CONFIGURATION');
        
        if($seats > $config->max_parallel_bookings){
            return ErrorUtils::renderCreateError('MAX_SEATS', ['max_seats' => $config->max_parallel_bookings]);
        }
        
        $validateOngoing = $this->validateOngoingBookings($config->max_ongoing_bookings, $customer);
        if($validateOngoing!==true){
            return $validateOngoing;
        }
                
        if($config->allow_booking_before_start_time_minutes && Carbon::now()->diffInMinutes($carbonStarts) < $config->allow_booking_before_start_time_minutes){
            return ErrorUtils::renderCreateError('BOOKING_TIME', ['min_booking_time' => $config->allow_booking_before_start_time_minutes]);
        }
        
        if($config->minimum_booking_time_minutes && $config->minimum_booking_time_minutes > $carbonStarts->diffInMinutes($carbonTo)){
            return ErrorUtils::renderCreateError('MIN_BOOKING_TIME', ['minimum_time' => $config->minimum_booking_time_minutes]);
        }

        if($config->maximum_booking_time_minutes && $config->maximum_booking_time_minutes < $carbonStarts->diffInMinutes($carbonTo)){
            return ErrorUtils::renderCreateError('MAX_BOOKING_TIME', ['maximum_time' => $config->maximum_booking_time_minutes]);
        }

        return true;
        
    }
    
    public function createNewBooking($customer, $starts, $ends, $seats = 1, $recurring_id = null, $options = null){
        if(!$this->getModelAvailability($starts, $ends)){
            return ErrorUtils::renderCreateError('MODEL_UNAVAILABLE');
        }
        if($options!==null){
            if(!Utils::isJson($options)){
                return ErrorUtils::renderCreateError('INVALID_JSON_OPTIONS');
            }
        }
        $validation = $this->validateWithConfiguration($customer, $starts, $ends, $seats);
        if($validation === true){
            $booking = new BookableBooking([
                'bookable_id' => $this->id,
                'bookable_type' => $this->bookableGetClassName(),
                'customer_id' => $customer->id,
                'customer_type' => $customer->bookableGetClassName(),
                'starts' => $starts,
                'ends' => $ends,
                'no_of_seats' => $seats,
                'bookable_recurring_booking_id' => $recurring_id,
                'options' => $options
            ]);
            
            $this->bookings()->save($booking);
            $customer->onBookingCreated($booking);
            return $booking;
        }else{
            return $validation;
        }      
    }        
}