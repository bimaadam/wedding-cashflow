<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemasukanKas extends Model
{
    public function event()
    {
        Model::unguard();
        return $this->belongsTo(Event::class);
    }

    //
}
