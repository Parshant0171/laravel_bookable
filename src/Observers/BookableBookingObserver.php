<?php

namespace Jgu\Bookable\Observers;

use Jgu\Bookable\Models\BookableBooking;
use Jgu\Bookable\Utils\DateUtils;


class BookableBookingObserver
{
    public function updating(BookableBooking $booking){        
        if(DateUtils::isTimeInThePast($booking->getOriginal('starts'))){
            if($booking->isDirty('starts') || 
                $booking->isDirty('ends') || 
                $booking->isDirty('bookable_type') || 
                $booking->isDirty('bookable_id') || 
                $booking->isDirty('customer_type') || 
                $booking->isDirty('customer_id') || 
                $booking->isDirty('no_of_seats') || 
                $booking->isDirty('bookable_configuration_slot_id') || 
                $booking->isDirty('bookable_recurring_booking_id'))
                return false;
        }
    }
}