<?php

namespace Modules\Gym\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GymMemberDiet extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
}
