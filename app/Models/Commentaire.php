<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    use HasFactory;
    protected $fillable = ['projet_id', 'habitant_id', 'contenu'];

    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }

    public function habitant()
    {
        return $this->belongsTo(Habitant::class);
    }
}
