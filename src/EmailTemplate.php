<?php

namespace Recca0120\EmailTemplate;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['slug', 'subject', 'from_address', 'from_name', 'content'];
}
