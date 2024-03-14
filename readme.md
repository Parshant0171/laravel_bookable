# Installation

Add the following code in your composer.json

```
"repositories": [
    ...
    { "type": "vcs", "url": "https://github.com/jgu-it/laravel_pkg_bookable.git" },
    ...
  ]
```
Run `composer install`
Run `php artisan vendor:publish` and select wfa from the list

Update the `yourapp/config/bookable.php` set `useTenants` => true if required.

Run `php artisan config:cache`

# Configuration

## `Bookable` Config Tables

1. `bookable_configurations`: Master configuration of a model that needs to become bookable.
2. `bookable_configuration_maps`: Mapping of Single Model to a configuration
3. `bookable_configuration_timings`: Available timings for one configuration. The timings added in this table for one configuration applies to all models mapped to this configuration.
4. `bookable_configuration_slots`: Available timings for one configuration. The timings added in this table for one configuration applies to all models mapped to this configuration.

### Bookable Configuration
`Jgu\Bookable\Models\BookableConfiguration`

Fields: 

| Item | Type | Required | Description
| --- | --- | --- | --- |
| name | String | Yes | NA |
| display_name | String | Yes | NA |
| model_path | String | Yes | path to the model which becomes bookable through this config |
| uses_slots | tinyint | No | Default: 0 <br/> if this configuration translates in to `slot booking` |
| is_form_builder | tinyint | No | Default: 0 <br/> flag to identify if this config is for a form builder |
| max_ongoing_bookings | tinyint | No | Default: 1 <br/> number of maximum ongoing bookings that the user can have |
| max_parallel_bookings | tinyint | No | Default: 1 <br/> number of maximum seats a user can book in one booking |
| uses_payments | tinyint | No | Default: 0 |
| unit_price | double | No | Default: 0 <br/> Unit price of one booking |
| tax_percentage | double | No | Default: 0 <br/> Tax percentage to be applied on the booking amount |
| uses_approvals | tinyint | No | Default: 0 <br/> to work in  coordination with workflow approval module |
| completely_open_ended | tinyint | No | Default: 0 <br/> if the model can be booked 24/7 365, change this to 1 |
| allow_seatwise_booking | tinyint | No | Default: 0 <br/> if there are multiple seats in a model that can be booked |
| allow_recurring_booking | tinyint | No | Default: 0 <br/> change this to 1 if the model allows reccuring bookings |
| allow_booking_before_start_time_minutes | int | No | Default: 0 <br/> defines till what time before the booking can this model be booked |
| open_booking_before_start_time_minutes | int | No | Default: null <br/> if not null, booking will be allowed only these many minutes before the start time of the booking |
| allow_cancellation_before_start_time_minutes | int | No | Default: 0 <br/> defines till what time before the booking can a booking on this model be cancelled |
| minimum_booking_time_minutes | int | No | Default: 0 <br/> if there is a minimum booking time |
| maximum_booking_time_minutes | int | No | Default: 0 <br/> if there is a maximum booking time |
| allow_bookings | tinyint | No | Default: 0 <br/> production flag to allow bookings or not |
| allow_cancellation | tinyint | No | Default: 0 <br/> production flag to allow cancellation or not |
| options | json | No | Frontend options that you may need to render UI |

### Bookable Configuration Maps
`Jgu\Bookable\Models\BookableConfigurationMap`

Fields: 

| Item | Type | Required | Description
| --- | --- | --- | --- |
| bookable_configuration_id | Relationship | Yes | ID of Bookable Configuration |
| mappable | Polymorphic Relationship | Yes | Model Type and ID to be mapped with Configuration |

### Bookable Configuration Timings
`Jgu\Bookable\Models\BookableConfigurationTiming`

Fields: 

| Item | Type | Required | Description
| --- | --- | --- | --- |
| bookable_configuration_id | Relationship | Yes | ID of Bookable Configuration |
| day_of_week | enum | Yes | one of the days of the week. repitition for one day for one config allowed. ensure there is no time overlap |
| start_time | datetime | Yes | start time of booking allowed concatinated with a dummy date | 
| end_time | datetime | Yes | end time of booking allowed concatinated with a dummy date | 

### Bookable Configuration Slots
`Jgu\Bookable\Models\BookableConfigurationTiming`

Fields:

| Item | Type | Required | Description
| --- | --- | --- | --- |
| bookable_configuration_id | Relationship | Yes | ID of Bookable Configuration |
| start_time | Date Time | Yes | Start Date & Time of this slot |
| end_time | Date Time | Yes | End Date & Time of this slot |
| mappable | Polymorphic Relationship | Yes | Model Type and ID to be mapped with Configuration |

# Transactional Data

## Tables
1. `bookable_bookings`: all booking data (be it for slot or open ended booking) goes in to this table
2. `bookable_recurring_bookings`: redundant data for recursive booking and other configuration for recursion is stored in this table

### Bookable Bookings
`Jgu\Bookable\Models\BookableBookings`

| Item | Type | Required | Booking Type | Description
| --- | --- | --- | --- | --- |
| bookable | Polymorphic Relationship | Yes | All | Model details of the bookable model |
| customer | Polymorphic Relationship | Yes | All | Customer details of the model booking |
| no_of_seats | int | No | All but recursive | Default: 1 <br/> no of seats booked |
| bookable_configuration_slot_id | Relationship | No | Slot Booking | Bookable Configuration Slot ID for the slot to be booked |
| bookable_recurring_booking_id | Relationship | No | Recursive Booking | Bookable Recuring Booking ID for the parent row in recuring booking table |
| starts | Date Time | Yes | All | Start Date & Time of the booking. Copied from slot in case of slot booking. |
| ends | Date Time | Yes | All | End Date & Time of this booking. Copied from slot in case of slot booking. |
| options | json | No | any | Frontend options that you may need to render UI |
| cancelled_at | Date Time | No | any | not null if the booking is cancelled |
| cancellable | Polymorphic Relationship | No | any | Customer details of the person who has cancelled the booking |

### Bookable Recurring Bookings
`Jgu\Bookable\Models\BookableRecurringBookings`

| Item | Type | Required | Description
| --- | --- | --- | --- |
| bookable | Polymorphic Relationship | Yes | Model details of the bookable model |
| customer | Polymorphic Relationship | Yes | Customer details of the model booking |
| no_of_seats | int | No | Default: 1 <br/> no of seats booked |
| starts | Date Time | Yes | Start Date & Time of the booking. |
| ends | Date Time | Yes  | End Date & Time of this booking. |
| options | json | No |  Frontend options that you may need to render UI |
| recurring_start_time | Time | Yes | Start Time of this booking every day. |
| recurring_end_time | Time | Yes  | End Time of this booking every day. |
| recurrs_monday, recurrs_tuesday, .... recurrs_sunday | tinyint | No | Default: false <br/> flags for everyday. true if booking recurrs on this day of week |
| cancelled_at | Date Time | No | any | not null if the booking is cancelled |
| cancellable | Polymorphic Relationship | No | any | Customer details of the person who has cancelled the booking |

The following traits are available and can be used in `Models`:

# Traits


## Bookable

### Where to `use`?

In the `model` which you want to make bookable.
Eg. Venues

### When to `use`?

1. When you want to allow customers to book these models
2. When the booking is simple:
  
  - few hours in a day
  - overnight booking <24 hours
  - multiday booking

### Available Methods:

1. Get Single Model Availability

```

public function getModelAvailability($fromDateTime, $toDateTime) : boolean

//Usage 

$v = Venue::find(1);
$v->getModelAvailability($fromDateTime, $toDateTime);

```

2. Get All Models Availability *(static function)*

```

public static function getAvailableModels($fromDateTime, $toDateTime, $models = null) : Array[Model]

//Usage

Venue::getAvailableModels($fromDateTime, $toDateTime);

```

Dummy Response (JSON Encoded)
```

[
  {
    id:1,
    ...model details...,
    availabile: boolean,
    seats_available: int //in case of seatwise booking only
  },
  {
    id:2,
    ...model details...,
    availabile: boolean,
    seats_available: int //in case of seatwise booking only
  },
  ...
]

```

3. Create New Booking

```
public function createNewBooking($customer, $starts, $ends, $seats = 1, $recurring_id = null, $options = null)

//usage

$customer = User::find(1);
$model = Venue::find(1);

$model->createNewBooking($customer, $starts, $ends, $seats /*to be used in case of seatwise booking*/, $recurring_id /*to be used in case of recurring bookings*/ );

```

## SeatwiseBookable

### Where to `use`?

In the `model` which you want to make bookable.
Eg. Venues

### Abstract Methods

`abstract public static function getSeatsKey();`

You need to define this method in your model that uses this trait.

Eg.

```

public static function getSeatsKey(){
    return 'size';
}

```

### When to `use`?

When you want to make a `Bookable` to be booked with seats.

### Available Methods:

All available methods in `Bookable` Trait

## ReccursiveBookable

### Where to `use`?

In the `model` which you want to make bookable.
Eg. Venues

### When to `use`?

When you want to allow a `Bookable` to be booked recursively. Please note: Recursive Bookings don't have seats.

### Available Methods:

All available methods in `Bookable` Trait

1. Get Model Availability

```
public function getModelReccursiveAvailability($startDate, $endDate, $startTime, $endTime, $rMon=false, $rTue=false, $rWed=false, $rThu=false, $rFri=false, $rSat=false, $rSun=false) : boolean

//Usage
$v = Venue::find(1);
$v->getModelReccursiveAvailability(...); // returns boolean
```

2. Create Recursive Booking

```
public function createReccursiveBooking($customer, $startDate, $endDate, $startTime, $endTime, $seats = 1, $rMon=false, $rTue=false, $rWed=false, $rThu=false, $rFri=false, $rSat=false, $rSun=false)

//Usage

$customer = User::find(1);
$model = Venue::find(1);

$model->createReccursiveBooking($customer, $startDate, $endDate, $startTime, $endTime, $seats, $rMon, $rTue, $rWed, $rThu, $rFri, $rSat, $rSun)

```

## SlotBookable

### Where to `use`?

In the `model` which you want to make bookable by slots.
Eg. Shuttle Booking / Office Hours

### When to `use`?

1. When you want to allow customers to book these models.
2. These models have slots and don't have open ended bookings.

### Available Methods:

1. Get Model Availability

```
public function getModelSlotAvailability($fromDateTime, $toDateTime) : boolean

//Usage

$s = Shuttle::find(1);
$s->getModelSlotAvailability($fromDateTime, $toDateTime); //returns boolean

```

2. Get All Models Availability *static function*

```
public static function getAvailableSlotModels($fromDateTime, $toDateTime, $models = null) : Array[Models]

//Usage

Shuttle::getAvailableSlotModels($fromDateTime, $toDateTime)
```

Dummy Response (JSON Encoded)

```
[
  {
    id:1,
    ...model details...,
    availabile: boolean,
    seats_available: int //in case of seatwise booking only
  },
  {
    id:2,
    ...model details...,
    availabile: boolean,
    seats_available: int //in case of seatwise booking only
  },
  ...
]
```

3. Create New Slot Booking

```
public function createNewSlotBooking($customer, $slot, $seats = 1, $options = null)

//Usage
$customer = User::find(1);
$slot = BookableConfigurationSlot::find(1);
$model = Shuttle::find(1);

$model->createNewSlotBooking($customer, $slot, $seats);

```

## SlotSeatwiseBookable

### Where to `use`?

In the `model` which you want to make bookable by slots.
Eg. Shuttle Booking / Office Hours

### Abstract Methods

`abstract public static function getSlotSeatsKey();`

You need to define this method in your model that uses this trait.

Eg.

```

public static function getSlotSeatsKey(){
    return 'size';
}

```


### When to `use`?

When you want to make a `SlotBookable` to have multiple seats.

### Available Methods:

All available methods in `SlotBookable` Trait

## HasBooking


### Where to `use`?

In the customer model i.e., the model that makes the booking
Eg. Users

### Abstract Methods

`public abstract function hasBookableCancelAdminRights() : boolean`

Eg. 
```
public function hasBookableCancelAdminRights(string $modelPath){
  return true; //if a user has admin rights
  return false; //for all other users
}
```

# Cancellation

1. Regular Booking


```

$b = BookableBooking::find(1);
$customer = User::find(1);
$b->cancelBooking($customer);

```

2. Recursive Booking

```

$b = BookableRecursiveBooking::find(1);
$customer = User::find(1);
$b->cancelBooking($customer);

```

# Events

Two events are fired

1. `onBookingCreated(BookableBooking $booking)`
2. `onBookingCancelled(BookableBooking $booking)`

Both the events are to be consumed by the Model class that uses the `HasBooking` trait.

# Payments

Usage:

```
$b = BookableBooking::find(1);
$b->getPricing()
```

Returns (json encoded):
```
{
  "total":2950,
  "price":2500,
  "tax":450,"seats":5,
  "unit_price":500,
  "tax_precentage":18
}
```

# Retrieve Bookings

You can retrieve the bookings of the customer (user who does the booking) or the model that gets booked.

```
public function getBookings(string $cancelPolicy = 'none' /* 'all' | 'none' | 'only */, Array $filters = [], bool $paginate = true)
```

| Param | Type | Required | Description |
| --- | --- | --- | --- |
| $cancelPolicy | enum <'none' or 'all' or 'only'> | No <br/> Default: 'none' | if 'none': only non cancelled bookings will be retreived. <br/> if 'all': all bookings will be retrieved. <br/> if 'only': only cancelled bookings will be retreived |
| $filters | Array<Array<string>> | No <br/> Default: [] | Very similar to the query builder of laravel. Retreival query can be altered using `$filters`. if $filter = `[['starts', '>', 'xx:xx:xx'], ['ends', '<', 'yy:yy:yy']]`, only the bookings between xx:xx:xx and yy:yy:yy will be retreived. <br/> Please note that the filter can only be applied to the columns present in the `bookable_bookings` table|
| $paginate | boolean | No. <br/> Default: true | Whether or not to apply pagination. |

## Usage

```
$customer = User::find(1);
function($query){$query->where('id',119);}
$b = $customer->getBookings('all', [['bookable_configuration_slot_id', '=', 2]]);

$model = TestVenue::find(1);
$b = $model->getBookings('none', [['bookable_configuration_slot_id', '=', 2]]);
echo json_encode($b);
```

# Model Global Scopes

Both `BookableBooking` and `BookableRecursiveBooking` have 'not_cancelled' global scope added to the model.

By default only 'not_cancelled' bookings are returned. If you need to display cancelled bookings separately, please remove the global scope by adding a piece of code to your logic.

# Errors
Please note: These errors will be returned in an object (not associative array)
```
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
        ]        
    ];
```
