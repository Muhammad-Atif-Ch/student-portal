<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileUpload
{

    public function upload(string $path, $image): string
    {
        try {
            $filNameWithExtension = $image->getClientOriginalName();
            $fileName = pathinfo($filNameWithExtension, PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $image1 = trim(str_replace(' ', '', $fileName . '_' . time() . '.' . $extension));
            $image->storeAs($path, $image1, 's3');
            Storage::disk('s3')->setVisibility($path, 'public');
            return $image1;
        } catch (\Exception $e) {
            throw new HttpException('Unable to upload file. ' . $e->getMessage());
        }
    }

    public function uploadModelImage(string $path, $image): string
    {
        $filNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($filNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        if ($extension == 'obj' || $extension == 'gltf') {
            $image1 = trim(str_replace(' ', '', $fileName . '_' . time() . '.' . $extension));
            $image->storeAs($path, $image1, 's3');
            Storage::disk('s3')->setVisibility($path, 'public');
            return $image1;
        } else {
            throw new NotFoundHttpException('The model image field must be a file of type: obj, gltf.');
        }
    }

    public function uploadMany(string $path, array $images, $userId = null): array
    {
        try {
            $uploadedAttachments = [];
            foreach ($images as $image) {
                $filNameWithExtension = $image->getClientOriginalName();
                $fileName = pathinfo($filNameWithExtension, PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                if ($userId) {
                    $image1 = $userId . '.' . $extension;
                } else {
                    $image1 = trim(str_replace(' ', '', $fileName . '_' . time() . '.' . $extension));
                }
                $image->storeAs($path, $image1, 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $uploadedAttachments[] = [
                    'file' => $image1,
                    'type' => $extension
                ];
            }

            return $uploadedAttachments;
        } catch (\Exception $e) {
            throw new HttpException('Unable to upload file. ' . $e->getMessage());
        }
    }

    public function uploadAttachments(string $path, $image): array
    {
        try {
            $filNameWithExtension = $image->getClientOriginalName();
            $fileName = pathinfo($filNameWithExtension, PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $image1 = trim(str_replace(' ', '', $fileName . '_' . time() . '.' . $extension));
            $image->storeAs($path, $image1, 's3');
            Storage::disk('s3')->setVisibility($path, 'public');
            $image2 = [
                'file' => $image1,
                'type' => $extension,
            ];
            return $image2;
        } catch (\Exception $e) {
            throw new HttpException('Unable to upload file. ' . $e->getMessage());
        }
    }
}
