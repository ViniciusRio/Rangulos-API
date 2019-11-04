<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $dates = [
      'start_date',
      'end_date'
    ];


    public function userCreator() 
    {
        return $this->beLongsTo(User::class, 'user_creator_id');
    }

    public  function guests()
    {
        return $this->hasMany(Guest::class, 'event_id', 'id');
    }

}
