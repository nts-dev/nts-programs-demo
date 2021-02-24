<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    public function events()
    {
        return $this->belongsToMany(Day::class);
    }
}
