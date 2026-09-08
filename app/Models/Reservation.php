<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Table;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable=[
        'customer_id',
        'year',
        'payment_status',
        'total_amount',
        'amount_paid',
        'paid_at',
        'notes'

    ];
    protected $casts=[
        'paid_at'=>'datetime',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);

    }
    public function tables(){
        return $this->belongsToMany(Table::class,'reservation_tables')->withPivot('year')->withTimestamps();
    }
}
