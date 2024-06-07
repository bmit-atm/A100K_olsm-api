<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdGroup extends Model
{
    use HasFactory;

    protected $table = 'ad_groups';

    protected $fillable = ['name'];
}
