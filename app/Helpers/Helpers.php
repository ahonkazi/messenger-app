<?php

function SendMail($subject,$data,$view,$to){
    $mailStatus = \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\DefaultMail($subject,$data,$view));
    if($mailStatus){
        return true;
    }else{
        return false;
    }
}