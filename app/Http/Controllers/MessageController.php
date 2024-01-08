<?php

namespace App\Http\Controllers;

use App\Http\Requests\createConversationRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\DeleteMessage;
use App\Models\DeleteMessageFile;
use App\Models\Media;
use App\Models\Message;
use App\Models\MessageFile;
use App\Models\SingleMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;

class MessageController extends Controller
{
    //send message
    public function sendMessage(createConversationRequest $request)
    {
        $conversation_text = '';
        if (!$request->hasFile('files') && !$request->has('message')) {
            return response()->json(['status' => false, 'message' => 'No message or file found'], 404);
        } elseif ($request->has('message')) {
            $conversation_text = $request->message;
        } else {
            if ($request->hasFile('files')) {
                $accepted_images = ['png', 'jpg', 'jpeg', 'webp'];
                $file_len = count($request->file('files'));
                if ($file_len > 1) {
                    $conversation_text = 'sent ' . $file_len . ' files';
                } else {
                    $ext = $request->file('files')[0]->getClientOriginalExtension();
                    if (in_array($ext, $accepted_images)) {
                        $conversation_text = 'sent a photo';
                    } elseif ($ext == 'mp4') {
                        $conversation_text = 'sent a video';
                    } elseif ($ext == 'mp3') {
                        $conversation_text = 'sent a voice message';
                    } elseif ($ext == 'pdf') {
                        $conversation_text = 'sent a pdf';

                    } else {
                        $conversation_text = 'sent a document';

                    }
                }

            }
        }
        $sender_id = Auth::user()->id; //auth user
        $receiver = User::where('unique_id', $request->unique_id)->first(); //from request
        if ($receiver == null) {
            return response()->json(['status' => false, 'message' => 'No User Found']);
        }
        $receiver_id = $receiver->id; //from request
        $old_conversation = Conversation::where(function ($query) use ($sender_id,$receiver_id) {
            $query->where('first_participant',$sender_id)
          ->where('second_participant', $receiver_id);
        })->orWhere(function ($query) use ($sender_id,$receiver_id) {
            $query->where('first_participant',$receiver_id)
          ->where('second_participant', $sender_id);
        })->first();
        return $old_conversation;

    }


}



