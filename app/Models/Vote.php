<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }

    public function habitant()
    {
        return $this->belongsTo(Habitant::class);
    }
}
