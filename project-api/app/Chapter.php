<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $guarded = ['id'];

    public function author()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function document()
    {
        return $this->belongsTo('App\Document');
    }


    public function media()
    {
        return $this->belongsToMany(Media::class)->withTimestamps();
    }
}
