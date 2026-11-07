<?php

namespace Modules\FieldForce\Entities;

use Illuminate\Database\Eloquent\Model;

class FieldForce extends Model
{
    protected $guarded = ['id'];

    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'assigned_to');
    }
}
