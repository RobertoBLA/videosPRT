<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class video extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'url',
        'order',
        'autplay',
        'loop',
        'auto_next',
        'status'
    ];
}
