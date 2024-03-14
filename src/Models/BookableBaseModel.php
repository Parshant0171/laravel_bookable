<?php

namespace Jgu\Bookable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Yajra\Auditable\AuditableWithDeletesTrait;

if(config('bookable.useTenants') && trait_exists('\App\Traits\ExTrait')){
    class BookableBaseModel extends Model
    {
        use HasFactory;
        use AuditableWithDeletesTrait, SoftDeletes;
        use \App\Traits\ExTrait;     

    }
}else{
    class BookableBaseModel extends Model
    {
        use HasFactory;
        use AuditableWithDeletesTrait, SoftDeletes;

    }
}