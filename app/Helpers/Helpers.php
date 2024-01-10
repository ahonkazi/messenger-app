<?php

function SendMail($subject,$data,$view,$to){
    $mailStatus = \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\DefaultMail($subject,$data,$view));
    if($mailStatus){
        return true;
    }else{
        return false;
    }
}

function UploadFile($prefix = 'messenger-img', $file, $path = 'files')
{
    $fileName = $prefix . random_int(111111, 9999999) . time() . '.' . $file->getClientOriginalExtension();
    $uploadStatus = $file->storeAs($path, $fileName, 'public');
    if ($uploadStatus) {
        return '/' . $path . '/' . $fileName;
    } else {
        return false;
    }
}

function removeFile($filePath)
{
    $fullpath = storage_path('app/public' . $filePath);
    if (\Illuminate\Support\Facades\File::exists($fullpath)) {
        unlink($fullpath);
    }
    return true;
}