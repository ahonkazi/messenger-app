<?php

namespace App\Http\Controllers;

use App\Http\Requests\createConversationRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\SingleMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    //
    public function createConversation(createConversationRequest $request)
    {
        $sender_id = Auth::user()->id; //auth user
        $receiver_id = User::where('unique_id', $request->unique_id)->first()->id; //from request
        if ($receiver_id == null) {
            return response()->json(['status' => false, 'message' => 'No User Found']);
        } else {

            $matchThese = ['first_participant' => $sender_id, 'second_participant' => $receiver_id];
            // if you need another group of wheres as an alternative:
            $orThose = ['first_participant' => $receiver_id, 'second_participant' => $sender_id];
            $old_conversation = Conversation::where($matchThese)->orWhere($orThose)->first();
            if ($old_conversation) {
                //     checkout old message container
                $senderMessageContainer = Message::where('sender_id',$sender_id)->latest()->first();
                $receiverMessageContainer = Message::where('sender_id',$receiver_id)->latest()->first();
            if($senderMessageContainer ==null){
                $messageStatus = $this->createNewMessage($sender_id, $receiver_id, $request->message);
                if ($messageStatus) {
                    $old_conversation->message = $request->message;
                    $old_conversation->message_time = now();
                    $updateStatus = $old_conversation->save();
                    if ($updateStatus) {
                        return response()->json(['status' => true, 'message' => 'Message sent', 'data' => $old_conversation]);

                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong']);

                    }

                } else {
                    return response()->json(['status' => false, 'message' => 'Something went wrong']);
                }
        }else{
                if((now()->timestamp - $senderMessageContainer->created_at->timestamp >86400) ||( $receiverMessageContainer->id > $senderMessageContainer->id) ){
                    $messageStatus = $this->createNewMessage($sender_id, $receiver_id, $request->message);
                    if ($messageStatus) {
                        $old_conversation->message = $request->message;
                        $old_conversation->message_time = now();
                        $updateStatus = $old_conversation->save();
                        if ($updateStatus) {
                            return response()->json(['status' => true, 'message' => 'Message sent', 'data' => $old_conversation]);

                        } else {
                            return response()->json(['status' => false, 'message' => 'Something went wrong']);

                        }

                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong']);
                    }
                }else{

                    $messageStatus = $this->createSingleMessage($sender_id, $receiver_id, $request->message,$senderMessageContainer->id);
                    if ($messageStatus) {
                        $old_conversation->message = $request->message;
                        $old_conversation->message_time = now();
                        $updateStatus = $old_conversation->save();
                        if ($updateStatus) {
                            return response()->json(['status' => true, 'message' => 'Message sent', 'data' => $old_conversation]);

                        } else {
                            return response()->json(['status' => false, 'message' => 'Something went wrong']);

                        }

                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong']);
                    } 
                }
                      
            }
                
                
                
        

            } else {
                $messageStatus = $this->createNewMessage($sender_id,$receiver_id,$request->message);
                if ($messageStatus) {
                    $newConversation = new Conversation();
                    $newConversation->message = $request->message;
                    $newConversation->message_time = now();
                    $newConversation->first_participant = $sender_id;
                    $newConversation->second_participant = $receiver_id;
                    $status = $newConversation->save();
                    if ($status) {
                        $sender_as_participant = new ConversationParticipant();
                        $sender_as_participant->conversation_id = $newConversation->id;
                        $sender_as_participant->participant_id = $sender_id;
                        $sender_as_participant->last_typing = null;
                        $status1 = $sender_as_participant->save();
                        $receiver_as_participant = new ConversationParticipant();
                        $receiver_as_participant->conversation_id = $newConversation->id;
                        $receiver_as_participant->participant_id = $receiver_id;
                        $receiver_as_participant->last_typing = null;
                        $status2 = $receiver_as_participant->save();
                        if ($status1 && $status2) {
                            return response()->json(['status' => true, 'message' => 'Conversation created', 'data' => $newConversation]);

                        } else {
                            return response()->json(['status' => false, 'message' => 'Something went wrong']);

                        }
                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong']);

                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Something went wrong']);
                }


            }
        }
    }

    function createNewMessage($sender_id, $receiver_id, $message)
    {
        $messageStatus = Message::create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id
        ]);

        if ($messageStatus) {
            $singleMessage = SingleMessage::create([
                'message_id' => $messageStatus->id,
                'message' => $message,
                'message_status' => 'sent',
                'has_file' => false,
            ]);
            if ($singleMessage) {
                return true;
            } else {
                return false;
            }

        }

    }
        function createSingleMessage($sender_id, $receiver_id, $message,$message_id)
        {
                $singleMessage = SingleMessage::create([
                'message_id' => $message_id,
                'message' => $message,
                'message_status' => 'sent',
                'has_file' => false,
            ]);
                if ($singleMessage) {
                    return true;
                } else {
                    return false;
                }

            }

        
    
    

}
