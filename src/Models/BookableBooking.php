<?php

namespace Jgu\Bookable\Models;

use Jgu\Bookable\Traits\CancelableBooking;
use Illuminate\Database\Eloquent\Builder;
use Jgu\Bookable\Utils\ErrorUtils;
use Jgu\Wfa\Traits\Approvable;

class BookableBooking extends BookableBaseModel
{    
    use CancelableBooking;
    use Approvable;

    protected static function booted()
    {
        static::addGlobalScope('not_cancelled', function (Builder $builder) {
            $builder->whereNull('cancelled_at');
            // $builder->where('cancelled_at', 'is', 'null');
        });
    }

    public function scopeCancelled($query){
        $query->whereNotNull('cancelled_at');
    }
    
    public function bookable(){
        return $this->morphTo();
    }

    public function customer(){
        return $this->morphTo();
    }

    public function slot(){
        return $this->belongsTo(BookableConfigurationSlot::class, 'bookable_configuration_slot_id');
    }

    public function recurringBooking(){
        return $this->belongsTo(BookableRecurringBooking::class, 'bookable_recurring_booking_id');
    }

    public function getBookableConfiguration(){        
        if ($this->bookable_configuration_slot_id!=null){
            $this->load('bookable.configurationSlots.configuration');
            if($this->bookable->configurationSlots && sizeof($this->bookable->configurationSlots)>0){
                return $this->bookable->configurationSlots[0]->configuration;
            }
        }else{
            $this->load('bookable.configurationMap.configuration');
            if($this->bookable->configurationMap && $this->bookable->configurationMap->configuration){
                return $this->bookable->configurationMap->configuration;
            }
        }
        return null;
    }

    public function getPricing(){
        $config = $this->getBookableConfiguration();
        if($config!=null){
            if($config->uses_payment==1){
                $price = $this->no_of_seats * $config->unit_price;
                $tax = $config->tax_percentage * $price / 100;
                return (object) [
                    'total' => $price + $tax,
                    'price' => $price,
                    'tax' => $tax,
                    'seats' => $this->no_of_seats,
                    'unit_price' => $config->unit_price,
                    'tax_precentage' => $config->tax_percentage
                ];
            }else{
                return ErrorUtils::renderCommonError('PAYMENT_UNAVAILABLE');
            }
        }else{
            return ErrorUtils::renderCommonError('CONFIG_ERROR');
        }
    }

    protected $guarded = [];
}
