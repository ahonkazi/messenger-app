<?php

namespace App\Http\Controllers;

use App\Http\Requests\createConversationRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Media;
use App\Models\Message;
use App\Models\MessageFile;
use App\Models\SingleMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    //
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
                $senderMessageContainer = Message::where('sender_id', $sender_id)->latest()->first();
                $receiverMessageContainer = Message::where('sender_id', $receiver_id)->latest()->first();
                if ($senderMessageContainer == null) {
                    $messageStatus = $this->createNewMessage($request, $sender_id, $receiver_id);
                    if ($messageStatus) {
                        $old_conversation->message = $conversation_text;
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
                } else {
                    $date1 = new \DateTime($senderMessageContainer->created_at->format('y-m-d'));
                    $date2 = new \DateTime(now()->format('y-m-d'));

                    $interval = $date1->diff($date2);
                    $differenceInDays = $interval->days;
                    if ($receiverMessageContainer) {

                        if (($differenceInDays > 1) || ($receiverMessageContainer->id > $senderMessageContainer->id)) {
                            $messageStatus = $this->createNewMessage($request, $sender_id, $receiver_id);
                            if ($messageStatus) {
                                $old_conversation->message = $conversation_text;
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
                        } else {

                            $messageStatus = $this->createSingleMessage($request, $sender_id, $receiver_id, $senderMessageContainer->id, $conversation_text);
                            if ($messageStatus) {
                                $old_conversation->message = $conversation_text;
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
                    } else {
                        if ($differenceInDays > 1) {
                            $messageStatus = $this->createNewMessage($request, $sender_id, $receiver_id);
                            if ($messageStatus) {
                                $old_conversation->message = $conversation_text;
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
                        } else {

                            $messageStatus = $this->createSingleMessage($request, $sender_id, $receiver_id, $senderMessageContainer->id, $conversation_text);
                            if ($messageStatus) {
                                $old_conversation->message = $conversation_text;
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


                }


            } else {
                $messageStatus = $this->createNewMessage($request, $sender_id, $receiver_id);
                if ($messageStatus) {
                    $newConversation = new Conversation();
                    $newConversation->message = $conversation_text;
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

    function createNewMessage($request, $sender_id, $receiver_id)
    {
//        $messageStatus = Message::create([
//            'sender_id' => $sender_id,
//            'receiver_id' => $receiver_id
//        ]);
        $messageStatus = new Message();
        $messageStatus->sender_id = $sender_id;
        $messageStatus->receiver_id = $receiver_id;
        $setMessaged = $messageStatus->save();
        try {
            if ($setMessaged) {
                $singleMessage = new SingleMessage();
                $singleMessage->message_id = $messageStatus->id;
                if ($request->has('message')) {
                    $singleMessage->message = $request->message;
                }
                $singleMessage->message_status = 'sent';
                $singleMessage->has_file = false;
                $saved = $singleMessage->save();
                if ($saved) {
                    if ($request->hasFile('files')) {
                        $accepted_files = ['png', 'jpg', 'jpeg', 'webp', 'mp4', 'mp3', 'pdf'];
                        $accepted_images = ['png', 'jpg', 'jpeg', 'webp'];
                        foreach ($request->file('files') as $file) {
                            $extension = $file->getClientOriginalExtension();
                            $file_type = '';
                            $file_path = '';
                            if (in_array($extension, $accepted_images)) {
                                $file_type = 'image';
                                $file_path = 'images';
                            } elseif ($extension == 'mp4') {
                                $file_type = 'video';
                                $file_path = 'videos';
                            } elseif ($extension == 'mp3') {
                                $file_type = 'audio';
                                $file_path = 'audios';
                            } elseif ($extension == 'pdf') {
                                $file_type = 'pdf';
                                $file_path = 'documents';
                            } else {
                                $file_type = 'document';
                                $file_path = 'documents';
                            }
                            $uploaded_file = UploadFile('messenger-' . $file_type . '-', $file, $file_path);
                            if ($uploaded_file) {
                                $media = new Media();
                                $media->file_type = $file_type;
                                $media->url = $uploaded_file;
                                $media->sender_id = $sender_id;
                                $media->receiver_id = $receiver_id;
                                $file_set_status = $media->save();
                                if ($file_set_status) {
                                    $message_file = new MessageFile();
                                    $message_file->single_message_id = $singleMessage->id;
                                    $message_file->file_type = $file_type;
                                    $message_file->media_id = $media->id;
                                    $message_file->save();

                                }

                            }

                        }
                        $singleMessage->has_file = true;
                        $singleMessage->save();


                    }

                } else {
                    $messageStatus->delete();
                    return false;

                }

            } else {
                return false;
            }
            return true;
        } catch (\Exception $exception) {
            return false;
        }

    }

    function createSingleMessage($request, $sender_id, $receiver_id, $message_id)
    {

        try {

            $singleMessage = new SingleMessage();
            $singleMessage->message_id = $message_id;
            if ($request->has('message')) {
                $singleMessage->message = $request->message;
            }
            $singleMessage->message_status = 'sent';
            $singleMessage->has_file = false;
            $saved = $singleMessage->save();
            if ($saved) {
                if ($request->hasFile('files')) {
                    $accepted_files = ['png', 'jpg', 'jpeg', 'webp', 'mp4', 'mp3', 'pdf'];
                    $accepted_images = ['png', 'jpg', 'jpeg', 'webp'];
                    foreach ($request->file('files') as $file) {
                        $extension = $file->getClientOriginalExtension();
                        $file_type = '';
                        $file_path = '';
                        if (in_array($extension, $accepted_images)) {
                            $file_type = 'image';
                            $file_path = 'images';
                        } elseif ($extension == 'mp4') {
                            $file_type = 'video';
                            $file_path = 'videos';
                        } elseif ($extension == 'mp3') {
                            $file_type = 'audio';
                            $file_path = 'audios';
                        } elseif ($extension == 'pdf') {
                            $file_type = 'pdf';
                            $file_path = 'documents';
                        } else {
                            $file_type = 'document';
                            $file_path = 'documents';
                        }
                        $uploaded_file = UploadFile('messenger-' . $file_type . '-', $file, $file_path);
                        if ($uploaded_file) {
                            $media = new Media();
                            $media->file_type = $file_type;
                            $media->url = $uploaded_file;
                            $media->sender_id = $sender_id;
                            $media->receiver_id = $receiver_id;
                            $file_set_status = $media->save();
                            if ($file_set_status) {
                                $message_file = new MessageFile();
                                $message_file->single_message_id = $singleMessage->id;
                                $message_file->file_type = $file_type;
                                $message_file->media_id = $media->id;
                                $message_file->save();

                            }

                        }

                    }
                    $singleMessage->has_file = true;
                    $singleMessage->save();


                }

            } else {
                return false;

            }


            return true;
        } catch (\Exception $exception) {
            return false;
        }

    }

    public function test(Request $request)
    {
        return response()->json(['data' => $request->files]);
    }


}
