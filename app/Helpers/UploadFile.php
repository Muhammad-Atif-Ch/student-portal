<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadFile
{
    public function upload(string $path, UploadedFile $image)
    {
        $start = microtime(true);
        if (! $image->isValid()) {
            throw new \RuntimeException('Uploaded file is not valid.');
        }

        $sizeKb = round($image->getSize() / 1024, 2); 

        $fileNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();

        $imageName = trim(str_replace(' ', '', $fileName.'_'.time().'.'.$extension));

        // Absolute path avoids CWD-dependent resolution and the slow
        // copy()+unlink() fallback move() takes when rename() can't be used.
        $image->move(public_path($path), $imageName);

        Log::info('[UploadFile] File moved to local disk', [
            'file' => $imageName,
            'size_kb' => $sizeKb,
            'move_ms' => round((microtime(true) - $start) * 1000, 2),
        ]);

        // For AWS
        // $path = $image->storeAs('/attachments', $imageName);
        // Storage::disk('s3')->setVisibility($path, 'public');

        return $imageName;
    }
}
