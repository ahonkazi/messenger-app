<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageFile extends Model
{
    use HasFactory;
    protected $fillable = [
        "single_message_id",
        "caption",
        "file_type",
        "media_id"

    ];
}
