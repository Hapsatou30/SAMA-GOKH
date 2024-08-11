<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Projet extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=[];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }
}
