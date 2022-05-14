<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class People extends Eloquent
{
    use softDeletes;
    protected $table = 'people';

    protected $fillable = [
        'id',
        'uuid',
        'name',
        'lastNameP',
        'lastNameM',
        'gender',
        'birthDate',
        'phone',
        'curp',
        'rfc',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
