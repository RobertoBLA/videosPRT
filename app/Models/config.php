<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class config extends Model
{

    protected $table = 'repconfig'; 
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'autoplay',
        'loop',
        'auto_next',
    ];
}
