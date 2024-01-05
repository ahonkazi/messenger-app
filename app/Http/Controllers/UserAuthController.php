<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignupOtpSendRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserSignupRequest;
use App\Models\ActiveStatusLog;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    //
    public function sendSignupOtp(SignupOtpSendRequest $request)
    {
        $otp = random_int(111111, 999999);
        $data = ['otp' => $otp];
        $mailStatus = SendMail('Signup otp mail', $data, 'mail.signupotp', $request->email);

        if ($mailStatus) {
            $prevOtp = Otp::all()->where('email', $request->email)->where('type', 'signup')->first();

            if ($prevOtp) {
                $prevOtp->delete();

                $status = Otp::create([
                    'email' => $request->email,
                    'otp_code' => $otp,
                    'type' => 'signup',
                ]);

                if ($status) {
                    return response()->json(['status' => true, 'message' => 'Send otp Success'], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Failed to send otp'], 500);
                }
            } else {
                $status = Otp::create([
                    'email' => $request->email,
                    'otp_code' => $otp,
                    'type' => 'signup',
                ]);

                if ($status) {
                    return response()->json(['status' => true, 'message' => 'Send otp Success'], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Failed to send otp'], 500);
                }
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Failed to send otp'], 500);
        }
    }

    public function signup(UserSignupRequest $request)
    {
        $otpItem = Otp::where('email', $request->email)->where('type', 'signup')->first();
        if ($otpItem != null) {
            if ($request->otp == $otpItem->otp) {
                if (time() - $otpItem->created_at->timestamp < 600) {
                    $user = new User();
                    $user->first_name = $request->first_name;
                    $user->gender = $request->gender;
                    $user->email = $request->email;
                    $user->date_of_birth = $request->date_of_birth;
                    $user->password = Hash::make($request->password);
                    if ($request->has('last_name')) {
                        $user->last_name = $request->last_name;
                    }
                    $user->unique_id = random_int(111111, 999999) . time();
                    $user->save();
                    if ($user) {
                        $loged = ActiveStatusLog::create(['user_id' => $user->id, 'last_active' => now()]);
                        $data = ['token' => $user->createToken("ACCESS-TOKEN")->accessToken];
                        if ($data && $loged) {
                            return response()->json(['status' => true, 'message' => 'Registration Successfull', 'data' => $data], 201);              

                        } else {
                            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

                        }
                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Otp Expired'], 410);

                }

            } else {
                return response()->json(['status' => false, 'message' => 'Invalid Otp Key'], 410);

            }
        } else {
            return response()->json(['status' => false, 'message' => 'No Otp Key Found With this email'], 404);
        }
    }

    public function login(UserLoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $log = ActiveStatusLog::where('user_id', $user->id)->first();
                if ($log) {
                    $log_status = $log->update(['last_active'=> now()]);
                    if ($log_status) {
                        $token = $user->createToken("ACCESS-TOKEN")->accessToken;
                        $data = ['token' => $token, 'user' => $user];
                        return response()->json(['status' => true, 'message' => 'Logged in Successfull', 'data' => $data], 200);

                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);

                }
            } else {
                return response()->json(['status' => false, 'message' => 'Incorrect password'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'No Account Found'], 404);
        }
    }


    public function logout()
    {
        $status = Auth::user()->token()->revoke();
        if ($status) {
            return response()->json(['status' => true, 'message' => 'Logout success'], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function authInfo()
    {
        return response()->json([
            'status' => true,
            'message' => 'Logged in',
            'data' => Auth::user()
        ], 200);
    }



    public function UpdateActiveLog(Request $request)
    {
        $user_id = Auth::user()->id;
        $prevLog = ActiveStatusLog::where('user_id', $user_id)->first();
            $status = $prevLog->update([
                'last_active' => now()
            ]);
            if ($status) {
                return response()->json(['status' => true], 200);
            } else {
                return response()->json(['status' => false], 500);
            }
       
    }

    public function getUser(Request $request)
    {
        if($request->has('unique_id')){
            $user = User::with('activeLog')->where('unique_id',$request->unique_id)->first();
           if($user){
               return response()->json(['user'=>$user,'is_me'=>false],200);
           }else{
               return response()->json(['status' => false,'message'=>'No user found'], 404);

           }
       }else{
            return response()->json(['user'=>Auth::user(),'is_me'=>true],200);
       }
    }
    
    public function test(){
        return response()->json(['data'=>now()->timestamp -Auth::user()->created_at->timestamp ]);
    }
}
