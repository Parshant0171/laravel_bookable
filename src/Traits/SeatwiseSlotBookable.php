<?php

namespace Jgu\Bookable\Traits;

trait SeatwiseSlotBookable {

    use SlotBookable;
    
    abstract public static function getSlotSeatsKey();
}