<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ["message", "message_time", "message_status", "first_participant", "second_participant", "sender_id"];

    public function you()
    {
        return $this->hasOne(ConversationParticipant::class, 'conversation_id');
    }
      public function partner()
      {
          return $this->hasOne(ConversationParticipant::class, 'conversation_id');
      }
    public function lastMessage()
    {
        return $this->hasOne(SingleMessage::class)->latest();
    }

}
