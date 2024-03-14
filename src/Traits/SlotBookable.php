<?php

namespace Jgu\Bookable\Traits;

use Jgu\Bookable\Models\BookableBooking;

use Carbon\Carbon;
use Jgu\Bookable\Models\BookableConfigurationSlot;

use Jgu\Bookable\Utils\Utils;
use Jgu\Bookable\Utils\DateUtils;
use Jgu\Bookable\Utils\ErrorUtils;

trait SlotBookable {  
    
    use BookableBase;

    public function slotBookableGetClassName(){
        return $this->className ?? get_class();
    }

    public function getTraitID(){
        return 'slot';
    }
    
    public function bookings(){
        return $this->morphMany(BookableBooking::class, 'bookable');
    }

    public function configurationSlots(){
        return $this->morphMany(BookableConfigurationSlot::class, 'mappable');
    }    

    public function getModelSlotAvailability($fromDateTime, $toDateTime){
        return self::getAvailableSlotModels($fromDateTime, $toDateTime, $this);
    }

    public static function getAvailableSlotModels($fromDateTime, $toDateTime, $models = null){
        $singleModel = false;
        if($models){
            $singleModel=true;            
        }else
            $models = self::all();
        $models->load(['configurationSlots' => function ($query) use ($fromDateTime, $toDateTime){
            $query->where('start_time', '>=', $fromDateTime)
                    ->where('end_time', '<=', $toDateTime);
        }, 
        'configurationSlots.configuration',
        'configurationSlots.bookings'
        ]);
        
        $singleModel && $models = [$models];
        foreach($models as $model){
            if($model->configurationSlots){
                foreach($model->configurationSlots as $slot){
                    if($slot->configuration->open_booking_before_start_time_minutes!=null && DateUtils::getDifferenceInMinutes($slot->start_time) > $slot->configuration->open_booking_before_start_time_minutes){
                        $slot->available = false;
                    }else{
                        if($slot->configuration->uses_approvals==1){
                            $bookingsSize = 0;
                            $totalSeatsBooked = 0;
                            if($slot->bookings && sizeof($slot->bookings)>0){
                                foreach($slot->bookings as $booking){
                                    if($booking->is_finally_approved){
                                        $bookingsSize++;
                                        $totalSeatsBooked+=$booking->no_of_seats;
                                    }                                                                
                                }
                            }
                        }else{
                            $bookingsSize = $slot->bookings ? sizeof($slot->bookings) : 0;
                        }
                        if($slot->configuration->allow_seatwise_booking == 1){
                            $slot->configuration->uses_approvals==0 && $slot->loadSum('bookings as total_seats_booked', 'no_of_seats');
                            $maxSeats = $model[self::getSlotSeatsKey()];
                            $slot->seats_available = $slot->configuration->uses_approvals==1 ? $maxSeats - $totalSeatsBooked : $maxSeats - $slot->total_seats_booked;
                            $slot->available = $slot->seats_available > 0;
                        } else { 
                            if($slot->bookings && $bookingsSize>0){
                                $slot->available = false;
                            }else{
                                $slot->available = true;
                            }
                        }
                    }                                        
                }
            }
        }        
        return $singleModel ? $models[0] : $models;
    }

    private function checkSelfAvailabilityForSlot($slot, $seats=1){
        $this->load(['configurationSlots' => function ($query) use ($slot) {
            $query->where('id', '=', $slot->id);
        },
        'configurationSlots.configuration',
        'configurationSlots.bookings'
        ]);                 
        
        if($this->configurationSlots && sizeof($this->configurationSlots)>0 && $this->configurationSlots[0]->configuration){            
            if($this->configurationSlots[0]->configuration->allow_seatwise_booking == 1){                
                $this->configurationSlots[0]->loadSum('bookings as total_seats_booked', 'no_of_seats');
                $maxSeats = $this[self::getSlotSeatsKey()];
                $totalSeatsBooked = $this->configurationSlots[0]->total_seats_booked ?? 0;                                 
                return $maxSeats >= $totalSeatsBooked + $seats;  
            }else{
                if($this->configurationSlots[0]->bookings && sizeof($this->configurationSlots[0]->bookings)>0){
                    return false;
                }else{
                    return true;
                }
            }
        }
        return false;
    }

    private function validateOngoingBookings($allowed, $customer, $config){        
        $query = BookableBooking::where('bookable_type', $this->slotBookableGetClassName())
                                            ->where('customer_id', $customer->id)
                                            ->where('customer_type', $customer->bookableGetClassName())
                                            ->where('ends', '>=',  Carbon::now());
    
        if($config->is_form_builder){
            $query->where('bookable_id', $this->id);
        }                                           
        
        $userBookings = $query->get();    
             
        if($userBookings->count()>=$allowed){            
            return ErrorUtils::renderCreateError('MAX_BOOKINGS', 
                ['ongoing_bookings' => $userBookings->count(),
                'max_bookings' => $allowed]);
        }
        return true;
    }
    
    private function validateWithConfiguration($customer, $starts, $ends, $seats, $slot){
        $carbon = new Carbon();
        $carbonStarts = $carbon->createFromDate($starts);
        $carbonTo = $carbon->createFromDate($ends);
        $config = null;
        $this->load([
            'configurationSlots.configuration'
        ]);
        
        if($this->configurationSlots && sizeof($this->configurationSlots)>0)
            $config = $this->configurationSlots[0]->configuration ?? null;

        if($config == null){
            return ErrorUtils::renderCreateError('INCORRECT_CONFIGURATION');
        }
        
        if($seats > $config->max_parallel_bookings){
            return ErrorUtils::renderCreateError('MAX_SEATS', ['max_seats' => $config->max_parallel_bookings]);
        }
        
        $validateOngoing = $this->validateOngoingBookings($config->max_ongoing_bookings, $customer, $config);
        if($validateOngoing!==true){            
            return $validateOngoing;            
        }
                
        if($config->allow_booking_before_start_time_minutes && Carbon::now()->diffInMinutes($carbonStarts) < $config->allow_booking_before_start_time_minutes){            
            return ErrorUtils::renderCreateError('BOOKING_TIME', ['min_booking_time' => $config->allow_booking_before_start_time_minutes]);
        }
                
        return true;
        
    }

    public function createNewSlotBooking($customer, $slot, $seats = 1, $options = null){        
        $starts = $slot->start_time;
        $ends = $slot->end_time;
        if(!$this->checkSelfAvailabilityForSlot($slot, $seats)){                        
            return ErrorUtils::renderCreateError('MODEL_UNAVAILABLE');
        }    
        if($options!==null){
            if(!Utils::isJson($options)){
                return ErrorUtils::renderCreateError('INVALID_JSON_OPTIONS');
            }
        }
        $validation = $this->validateWithConfiguration($customer, $starts, $ends, $seats, $slot);
        
        if($validation === true){
            $booking = new BookableBooking([
                'bookable_id' => $this->id,
                'bookable_type' => $this->slotBookableGetClassName(),
                'customer_id' => $customer->id,
                'customer_type' => $customer->bookableGetClassName(),
                'starts' => $starts,
                'ends' => $ends,
                'no_of_seats' => $seats,
                'bookable_configuration_slot_id' => $slot->id,
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