<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeleteMessage extends Model
{
    use HasFactory;
    protected $fillable=[
        "single_message_id",
        "participant_id"
    ];
}
