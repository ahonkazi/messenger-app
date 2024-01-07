<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    use HasFactory;
    protected $fillable = ["conversation_id","participant_id","last_typing","last_deleted_message_id"];
}
