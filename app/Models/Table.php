<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable=['table_code','x_position','y_position'];

    function reservations(){
        return $this->belongsToMany(Reservation::class,'reservation_tables')->withPivot('year')->withTimestamps();
    }
}
