<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class UploadFile
{
    public function upload(string $path, UploadedFile $image)
    {
        if (! $image->isValid()) {
            throw new \RuntimeException('Uploaded file is not valid.');
        }

        $fileNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();

        $imageName = trim(str_replace(' ', '', $fileName.'_'.time().'.'.$extension));

        // Absolute path avoids CWD-dependent resolution and the slow
        // copy()+unlink() fallback move() takes when rename() can't be used.
        $image->move(public_path($path), $imageName);

        // For AWS
        // $path = $image->storeAs('/attachments', $imageName);
        // Storage::disk('s3')->setVisibility($path, 'public');

        return $imageName;
    }
}
