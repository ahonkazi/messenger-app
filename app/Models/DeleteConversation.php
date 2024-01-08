<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeleteConversation extends Model
{
    use HasFactory;
    protected $fillable=[
        "conversation_id",
        "participant_id"
    ];
}
