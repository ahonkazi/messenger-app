<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeleteMessageFile extends Model
{
    use HasFactory;
    protected $fillable=[
        "message_file_id",
        "participant_id"
    ];
}
