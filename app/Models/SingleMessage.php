<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SingleMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        "message_id",
        "message",
        "message_status",
        "unsent",
        "has_file"
    ];
}
