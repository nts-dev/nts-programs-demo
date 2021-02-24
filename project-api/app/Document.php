<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = ['id'];

    public function author()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function chapters()
    {
        return $this->hasMany('App\Chapter')->withTimestamps();
    }

    public function projects()
    {
        return $this->belongsToMany('App\Project')->withTimestamps();
    }

    public function media()
    {
        return $this->belongsToMany(Media::class)->withTimestamps();
    }
}
