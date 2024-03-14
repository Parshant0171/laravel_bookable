<?php

namespace Jgu\Bookable\Traits;

use Jgu\Bookable\Models\BookableBooking;
use Jgu\Bookable\Utils\ErrorUtils;

trait BookableBase {

    public static $_FILTERS = [
        'id',
        'bookable_type',
        'bookable_id',
        'customer_type',
        'customer_id',
        'bookable_configuration_slot_id',
        'bookable_recurring_booking_id',
        'starts',
        'ends',
        'cancellable_type',
        'cancellable_id'
    ];

    public abstract function getTraitID();

    private function getInitQuery(bool $removeScope = false){
        switch($this->getTraitID()){
            case 'customer':
                return $removeScope ? 
                    BookableBooking::withoutGlobalScope('not_cancelled')->where('customer_type', $this->bookableGetClassName())->where('customer_id', $this->id) 
                    : BookableBooking::where('customer_type', $this->bookableGetClassName())->where('customer_id', $this->id);
            case 'model':
                return $removeScope ? 
                    BookableBooking::withoutGlobalScope('not_cancelled')->where('bookable_type', $this->bookableGetClassName())->where('bookable_id', $this->id) 
                    : BookableBooking::where('bookable_type', $this->bookableGetClassName())->where('bookable_id', $this->id);
            case 'slot':
                return $removeScope ? 
                BookableBooking::withoutGlobalScope('not_cancelled')->where('bookable_type', $this->slotBookableGetClassName())->where('bookable_id', $this->id)
                : BookableBooking::where('bookable_type', $this->slotBookableGetClassName())->where('bookable_id', $this->id);
            default:
                return null;
        }
    }

    public function getBookings(string $cancelPolicy = 'none' /* 'all' | 'none' | 'only */, Array $filters = [], bool $paginate = true){
        $query = $this->getInitQuery($cancelPolicy!=='none');
        if($query === null){
            return ErrorUtils::renderCommonError('BAD_REQUEST');
        }
        if($cancelPolicy === 'only')
            $query->cancelled();
        if(sizeof($filters) !== 0){
            foreach($filters as $filter){
                if(is_array($filter) && sizeof($filter)===3 && in_array($filter[0], self::$_FILTERS)){
                    $query->where($filter[0], $filter[1], $filter[2]);    
                }else{  
                    return ErrorUtils::renderCommonError('BAD_REQUEST');
                }
            }
        }
        $query->with(['slot.configuration', 'recurringBooking', 'bookable']);
        return $paginate ? $query->paginate() : $query->get();
    }

}