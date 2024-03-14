<?php

namespace Jgu\Bookable\Traits;

use Carbon\Carbon;

use Jgu\Bookable\Utils\ErrorUtils;

trait CancelableBooking {    

    public abstract function getBookableConfiguration();

    public function cancelBooking($customer){
        $config = $this->getBookableConfiguration();
        if($config->allow_cancellation == 1){
            if($config->allow_cancellation_before_start_time_minutes){
                $carbon = new Carbon();
                $carbonStarts = $carbon->createFromDate($this->starts);
                if(Carbon::now()->diffInMinutes($carbonStarts) < $config->allow_cancellation_before_start_time_minutes){
                    return ErrorUtils::renderCancelError('TIME_BEFORE_START', ['minimum_time' => $config->allow_cancellation_before_start_time_minutes]);
                }
            }

            if($this->customer_type != $customer->bookableGetClassName() || $this->customer_id != $customer->id){
                if(!$customer->hasBookableCancelAdminRights($this->bookable_type))
                    return ErrorUtils::renderCancelError('Unauthorized');
            }

            $this->cancelled_at = Carbon::now()->toDateTimeString();
            $this->cancellable_type = $customer->bookableGetClassName();
            $this->cancellable_id = $customer->id;

            $this->save();
            $customer->onBookingCancelled($this);
            return $this;
        }else{            
            return ErrorUtils::renderCancelError('NOT_ALLOWED');
        }
    }
}