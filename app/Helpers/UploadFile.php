<?php

namespace App\Helpers;


use Illuminate\Support\Facades\Storage;

class UploadFile
{
    public function upload($path, $image)
    {
        $filNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($filNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $imageName = trim(str_replace(' ', '', $fileName . '_' . time() . '.' . $extension));
        str_replace(' ', '', $imageName);
        if (!$image->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }

        //For Local Project
        $image->move($path, $imageName);


        //For AWS
        // $path = $image->storeAs('/attachments', $imageName);
        // Storage::disk('s3')->setVisibility($path, 'public');

        return $imageName;
    }

}