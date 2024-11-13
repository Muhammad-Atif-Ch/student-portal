<?php

namespace App\Observers;

use App\Models\Question;
use App\Services\FileUpload;

class QuestionObserver
{
    protected $request;
    /**
     * Handle the Question "created" event.
     */
    public function created(Question $question): void
    {
        if (isset($this->request->attachments)) {
            $this->attachments($question);
        }
    }

    /**
     * Handle the Question "updated" event.
     */
    public function updated(Question $question): void
    {
        //
    }

    /**
     * Handle the Question "deleted" event.
     */
    public function deleted(Question $question): void
    {
        //
    }

    /**
     * Handle the Question "restored" event.
     */
    public function restored(Question $question): void
    {
        //
    }

    /**
     * Handle the Question "force deleted" event.
     */
    public function forceDeleted(Question $question): void
    {
        //
    }

    public function attachments($data)
    {
        $fileUploader = app(FileUpload::class);
        $req['attachments']  = $fileUploader->uploadMany(env("ATTACHMENT_IMAGES_PATH"), $this->request->attachments);
        $data->attachments()->createMany($req['attachments']);
    }
}
