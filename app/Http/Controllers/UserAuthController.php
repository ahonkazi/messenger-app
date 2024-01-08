<?php

namespace App\Http\Controllers;

use App\Http\Requests\createConversationRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\DeleteConversation;
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


        $matchThese = ['first_participant' => $sender_id, 'second_participant' => $receiver_id];
        // if you need another group of wheres as an alternative:
        $orThose = ['first_participant' => $receiver_id, 'second_participant' => $sender_id];
        $old_conversation = Conversation::where($matchThese)->orWhere($orThose)->first();

        if ($old_conversation != null) {
        return 'found';

        } else {
        return 'not found';
        }
    }


//create new message and singlemessage
    function createNewMessage($request, $sender_id, $receiver_id): bool
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
                $singleMessage->sender_id = $sender_id;
                $singleMessage->receiver_id = $receiver_id;
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

//    message exists so create only single message
    function createSingleMessage($request, $sender_id, $receiver_id, $message_id): bool
    {
        try {
            $singleMessage = new SingleMessage();
            $singleMessage->message_id = $message_id;
            if ($request->has('message')) {
                $singleMessage->message = $request->message;
            }
            $singleMessage->message_status = 'sent';
            $singleMessage->has_file = false;
            $singleMessage->sender_id = $sender_id;
            $singleMessage->receiver_id = $receiver_id;
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

//    delete for me
    public
    function deleteForMe(Request $request, $id): JsonResponse
    {
        $user_id = Auth::user()->id;
        $message = SingleMessage::where('id', $id)->first();
        if ($message) {
            $DeleteMessaged = DeleteMessage::where('single_message_id', $message->id)->where('participant_id', $user_id)->first();
            if ($DeleteMessaged == null) {
                $deleted = DeleteMessage::create([
                    'single_message_id' => $message->id,
                    'participant_id' => $user_id
                ]);
                if ($deleted) {
                    return response()->json(['status' => true, 'message' => 'Deleted'], 200);

                } else {
                    return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

                }
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

            }
        } else {
            return response()->json(['status' => false, 'message' => 'No message found.'], 404);

        }
    }

//    delete file for me
    public
    function deleteFileForMe(Request $request, $id): JsonResponse
    {
        $user_id = Auth::user()->id;
        $message = MessageFile::where('id', $id)->first();
        if ($message) {
            $DeleteMessaged = DeleteMessageFile::where('message_file_id', $message->id)->where('participant_id', $user_id)->first();
            if ($DeleteMessaged == null) {
                $deleted = DeleteMessageFile::create([
                    'message_file_id' => $message->id,
                    'participant_id' => $user_id
                ]);
                if ($deleted) {
                    return response()->json(['status' => true, 'message' => 'Deleted'], 200);

                } else {
                    return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

                }
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

            }
        } else {
            return response()->json(['status' => false, 'message' => 'No file found.'], 404);

        }
    }

//get all messages
    public
    function messageList(Request $request, $unique_id): JsonResponse
    {
        $user_id = Auth::user()->id;
        $partner = User::where('unique_id', $unique_id)->first();
        if ($partner == null) {
            return response()->json(['status' => false, 'messages' => 'No partner found'], 200);
        }
        $conversation = Conversation::where(['first_participant' => $user_id, 'second_participant' => $partner->id])
            ->orWhere(['first_participant' => $partner->id, 'second_participant' => $user_id])->first();
        if ($conversation == null) {
            return response()->json(['status' => false, 'messages' => 'No Conversation found,please create one'], 200);
        }
        $limit = (int)$request->input('per_page', 2);
        $offset = (int)$request->input('page', 1);
        $skip = $limit * ($offset - 1);
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)->where('participant_id', $user_id)->first();
        $removedMessages = DeleteMessage::all()->where('participant_id', $user_id);
        $removedMessagesIds = [];
        $removedMessageFiles = DeleteMessageFile::all()->where('participant_id', $user_id);
        $removedMessageFilesIds = [];

        if ($removedMessageFiles) {
            foreach ($removedMessageFiles as $mgsFile) {
                array_push($removedMessageFilesIds, $mgsFile->message_file_id);
            }
        }
        if ($removedMessages) {
            foreach ($removedMessages as $mgs) {
                if ($mgs) {
                    array_push($removedMessagesIds, $mgs->single_message_id);
                }
            }
        }
        $messages = Message::with(['singleMessages' => function ($query) use ($removedMessagesIds, $removedMessageFilesIds, $participant) {
            $query->whereNotIn('id', $removedMessagesIds)
                ->where('id', '>', $participant->last_deleted_message_id)
                ->with(['messageFiles' => function ($query) use ($removedMessageFilesIds) {
                    $query->whereNotIn('id', $removedMessageFilesIds)
                        ->with('media')->latest();
                }])->latest();

        }])->
        where(function ($query) use ($user_id, $partner) {
            $query->where(['sender_id' => $user_id, 'receiver_id' => $partner->id])
                ->orWhere(['sender_id' => $partner->id, 'receiver_id' => $user_id]);
        })
            ->whereHas('singleMessages')
            ->latest()
            ->skip($skip)
            ->limit($limit)
            ->get();

        $filteredData = [];
        for ($i = 0; $i < count($messages); $i++) {
            if (count($messages[$i]->singleMessages)) {
                foreach ($messages[$i]->singleMessages as $mgs) {
                    if ($mgs->sender_id == $partner->id) {
                        $mgs->message_status = 'seen';
                        $mgs->save();
                    }
                }
                array_push($filteredData, $messages[$i]);

            }
        }
        $data = [];
        foreach ($filteredData as $message) {
            $date = $message->created_at->format('Y-m-d'); // Format date as needed

            // Find or create the date object within the data array:
            $dateIndex = array_search($date, array_column($data, 'date'));
            if ($dateIndex === false) {
                $data[] = ['date' => $date, 'messages' => []];
                $dateIndex = count($data) - 1;
            }
            // Add the message to the corresponding date's messages:
            $data[$dateIndex]['messages'][] = $message;
        }
        if ($conversation->sender_id == $partner->id) {
            $conversation->message_status = 'seen';
            $conversation->save();
        }

//return count($messages[1]->singleMessages);
        return response()->json(['status' => true, 'data' => $data, 'page_data' => ['per_page' => $limit, 'current_page' => $offset, 'next_page' => $offset + 1, 'skiped' => $skip]], 200);
        //
    }


//    clear messages
    public
    function clearConversationForMe(Request $request, $unique_id): JsonResponse
    {
        $user_id = Auth::user()->id;
        $partner = User::where('unique_id', $unique_id)->first();
        if ($partner == null) {
            return response()->json(['status' => false, 'messages' => 'No partner found'], 200);
        }
        $conversation = Conversation::where(['first_participant' => $user_id, 'second_participant' => $partner->id])
            ->orWhere(['first_participant' => $partner->id, 'second_participant' => $user_id])->first();
        if ($conversation == null) {
            return response()->json(['status' => false, 'messages' => 'No Conversation found,please create one'], 200);
        }
        $clearStatus = $this->clearConversation($user_id, $partner, $conversation);
        if ($clearStatus) {
            return response()->json(['status' => true, 'messages' => 'Conversation cleared'], 200);

        } else {
            return response()->json(['status' => false, 'messages' => 'Something went wrong'], 500);

        }

    }


    function clearConversation($user_id, $partner, $conversation): bool
    {


        $participant = ConversationParticipant::where('conversation_id', $conversation->id)->where('participant_id', $user_id)->first();
        $last_message = SingleMessage::where(['sender_id' => $user_id, 'receiver_id' => $partner->id])
            ->orWhere(['sender_id' => $partner->id, 'receiver_id' => $user_id])->latest()->first();
        $participant->last_deleted_message_id = $last_message->id;
        $saveStatus = $participant->save();
        return $saveStatus;

    }

    public function test()
    {
        $date = "2024-01-07T16:37:51.000000Z";
        $now = now();
    }

    public function deleteConversation(Request $request, $unique_id): JsonResponse
    {
        $user_id = Auth::user()->id;
        $partner = User::where('unique_id', $unique_id)->first();
        if ($partner == null) {
            return response()->json(['status' => false, 'messages' => 'No partner found'], 200);
        }
        $conversation = Conversation::where(['first_participant' => $user_id, 'second_participant' => $partner->id])
            ->orWhere(['first_participant' => $partner->id, 'second_participant' => $user_id])->first();
        if ($conversation == null) {
            return response()->json(['status' => false, 'messages' => 'No Conversation found,please create one'], 200);
        }
        $clearStatus = $this->clearConversation($user_id, $partner, $conversation);
        $deletedConversation = DeleteConversation::where(['conversation_id' => $conversation->id, 'participant_id' => $user_id])->first();
        if ($deletedConversation == null) {
            DeleteConversation::create([
                'conversation_id' => $conversation->id,
                'participant_id' => $user_id
            ]);
        }
        if ($clearStatus) {
            return response()->json(['status' => true, 'messages' => 'Conversation deleted'], 200);

        } else {
            return response()->json(['status' => false, 'messages' => 'Something went wrong'], 500);

        }
    }

    public function loadConveration(Request $request)
    {
        $limit = (int)$request->input('per_page', 2);
        $offset = (int)$request->input('page', 1);
        $skip = $limit * ($offset - 1);
        $user_id = Auth::user()->id;
        $removedConversation = DeleteConversation::all()->where('participant_id', $user_id);
        $removedConversationIds = [];
        if ($removedConversation) {
            foreach ($removedConversation as $conv) {
                array_push($removedConversationIds, $conv->conversation_id);
            }
        }
        $conversations = Conversation::with(['participants' => function ($query) use ($user_id) {
            $query->where('participant_id', '!=', $user_id)
                ->with('user_data');
        }])
            ->where(function ($query) use ($user_id) {
                $query->where('first_participant', $user_id)
                    ->orWhere('second_participant', $user_id);
            })
            ->whereNotIn('id', $removedConversationIds)
            ->latest('updated_at')->get();

        return response()->json(['status' => true, 'data' => $conversations, 'page_data' => ['per_page' => $limit, 'current_page' => $offset, 'next_page' => $offset + 1, 'skiped' => $skip]], 200);

    }
}
