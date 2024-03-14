<?php

namespace Jgu\Bookable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Jgu\Bookable\Models\BookableBooking;
use Jgu\Bookable\Models\BookableRecurringBooking;


trait HasBooking {

    use BookableBase;

    public abstract function hasBookableCancelAdminRights(string $modelPath);

    public abstract function onBookingCreated(BookableBooking $booking);
    public abstract function onBookingCancelled(BookableBooking $booking);

    public function getTraitID(){
        return 'customer';
    }

    public function bookableGetClassName(){
        return $this->className ?? get_class();
    }

    public function bookings(){
        return $this->morphMany(BookableBooking::class, 'customer');
    }

    public function recurringBookings(){
        return $this->morphMany(BookableBooking::class, 'customer');
    }
    
}