<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable=['name','phone','notes'];
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
