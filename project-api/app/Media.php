<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $guarded = ['id'];

    public function uploader()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class)->withTimestamps();
    }

    public function chapters()
    {
        return $this->belongsToMany(Chapter::class)->withTimestamps();
    }
}
