<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use SoftDeletes;

    protected $dates = [
      'start_date',
      'end_date',
      'delete_at'
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
