<?php

namespace Jgu\Bookable\Utils;

class ErrorUtils {

    public static $_ERRORS = [
        'CREATE_BOOKING' => [
            'MODEL_UNAVAILABLE' => ['description' => 'Model not available for booking.', 'type' => 'unavailable', 'code' => 'jgu/book/c/001'],
            'MAX_BOOKING_TIME' => ['description' => 'Model not allowed to be booked for more than [[maximum_time]] minutes.', 'type' => 'overbook', 'code' => 'jgu/book/c/002', 'var' => ['maximum_time']],
            'MIN_BOOKING_TIME' => ['description' => 'Model not allowed to be booked for less than [[minimum_time]] minutes.', 'type' => 'underbook', 'code' => 'jgu/book/c/003', 'var' => ['minimum_time']],
            'MAX_BOOKINGS' => ['description' => 'User already has [[ongoing_bookings]] ongoing bookings. A maximum of [[max_bookings]] ongoing bookings are allowed.', 'type' => 'ongoing_max', 'code' => 'jgu/book/c/004', 'var' => ['ongoing_bookings', 'max_bookings']],
            'INCORRECT_CONFIGURATION' => ['description' => 'Incorrect data configuration. Please contact system admin', 'type' => 'config_error', 'code' => 'jgu/book/c/005'],
            'MAX_SEATS' => ['description' => 'You can only book a maximum of [[max_seats]] at a time.', 'type' => 'max_seats', 'code' => 'jgu/book/c/006', 'var' => ['max_seats']],
            'BOOKING_TIME' => ['description' => 'You need to book at least [[min_booking_time]] minutes before start of booking.', 'type' => 'late_book', 'code' => 'jgu/book/c/007', 'var' => ['min_booking_time']],
            'INVALID_JSON_OPTIONS' => ['description' => 'Invalid JSON value provided for options.', 'type' => 'bad_input', 'code' => 'jgu/book/c/008'],
        ],
        'CANCEL_BOOKING' => [
            'NOT_ALLOWED' => ['description' => 'Cancellation not available for this booking.', 'type' => 'unavailable', 'code' => 'jgu/book/d/001'],
            'TIME_BEFORE_START' => ['description' => 'You can cancel booking only [[minimum_time]] minutes before the start time.', 'type' => 'late_cancel', 'code' => 'jgu/book/d/002', 'var' => ['minimum_time']],
            'Unauthorized' => ['description' => 'You are not authorized to cancel this booking.', 'type' => 'unauth', 'code' => 'jgu/book/d/003']
        ],
        'COMMONS' => [
            'CONFIG_ERROR' => ['description' => 'Incorrect data configuration. Please contact system admin', 'type' => 'config_error', 'code' => 'jgu/book/q/001'],
            'PAYMENT_UNAVAILABLE' => ['description' => 'Incorrect data configuration. Payment Not Configured', 'type' => 'config_error', 'code' => 'jgu/book/q/002'],
            'BAD_REQUEST' => ['description' => 'Bad request.', 'type' => 'bad_request', 'code' => 'jgu/book/q/003']
        ]        
    ];

    private static function renderError($masterType, $type, $vars = []){
        $error = self::$_ERRORS[$masterType][$type];
        foreach ($vars as $key => $val) {
            if( array_key_exists('var', $error) && in_array($key,$error['var'])){
                $error['description'] = str_replace('[[' . $key . ']]', $val, $error['description']);
            }
        }
        $error['error'] = true;
        return (object) $error;
    }

    public static function renderCreateError($type, $vars = []){
        return self::renderError('CREATE_BOOKING', $type, $vars);
    }

    public static function renderCancelError($type, $vars = []){
        return self::renderError('CANCEL_BOOKING', $type, $vars);
    }

    public static function renderCommonError($type, $vars = []){
        return self::renderError('COMMONS', $type, $vars);
    }

}