<?php

namespace App\Models;

use Database\Factories\AppFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    /** @use HasFactory<AppFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
    ];
}
