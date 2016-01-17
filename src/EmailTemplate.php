<?php

namespace Recca0120\EmailTemplate;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slug', 'subject', 'from_address', 'from_name', 'content'];
}
