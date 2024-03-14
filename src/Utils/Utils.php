<?php

namespace Jgu\Bookable\Utils;

use Carbon\Carbon;

class Utils {

    public static function timingsObject($from, $to, $useCarbon = true){        
        return $useCarbon ? (object) ["from"=> Carbon::createFromTimeString($from), "to" => Carbon::createFromTimeString($to)] : (object) ["from"=> $from, "to" => $to];
    }

    public static function timelineObject($from, $to, $status = "available"){
        return (object) ["from" => $from, "to" => $to, "status" => $status];
    }

    public static function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
     }

}