<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    protected $fillable = [
        'title',
        'about',
        'address',
        'price',
           'max_guests',
        'start_date',
        'end_date'
    ];

    protected $dates = [
      'start_date',
      'end_date'
    ];

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = Carbon::parse($value);
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = Carbon::parse($value);
    }


    public function userCreator()
    {
        return $this->beLongsTo(User::class, 'user_creator_id');
    }

    public  function guests()
    {
        return $this->hasMany(Guest::class, 'event_id', 'id');
    }

}
