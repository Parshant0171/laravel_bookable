<?php

namespace Jgu\Bookable\Traits;

trait SeatwiseBookable {

    use Bookable;
    
    abstract public static function getSeatsKey();
}