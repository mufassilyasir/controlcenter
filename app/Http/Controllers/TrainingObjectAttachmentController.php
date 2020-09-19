<?php

namespace App\Http\Controllers;

use App\File;
use App\Training;
use App\TrainingObject;
use App\TrainingReport;
use App\TrainingObjectAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use function MongoDB\BSON\toJSON;

class TrainingObjectAttachmentController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param TrainingObject $trainingObject
     * @return false|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, TrainingObject $trainingObject)
    {
        $this->authorize('create', TrainingObjectAttachment::class);

        $data = $request->validate([
            'file' => 'required|file|mimes:pdf,xls,xlsx,doc,docx,txt,png,jpg,jpeg',
            'hidden' => 'nullable'
        ]);

        $attachment_ids = self::saveAttachments($request, $trainingObject);

        if ($request->expectsJson()) {
            return json_encode([
                'id' => $attachment_ids,
                'message' => 'File(s) successfully uploaded'
            ]);
        }

        return redirect()->back()->withSuccess('Attachment successfully addded');

    }

    /**
     * Display the specified resource.
     *
     * @param \App\TrainingObjectAttachment $attachment
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(TrainingObjectAttachment $attachment)
    {
        $this->authorize('view', $attachment);

        return redirect(route('file.get', ['file' => $attachment->file]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param TrainingObjectAttachment $attachment
     * @return false|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Request $request, TrainingObjectAttachment $attachment)
    {
        $this->authorize('delete', $attachment);

        Storage::delete($attachment->file->full_path);
        $attachment->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Attachment successfully deleted']);
        }

        return redirect()->back()->withSuccess('Attachment successfully deleted');
    }

    /**
     * @param Request $request
     * @param TrainingObject $object
     * @return array
     */
    public static function saveAttachments(Request $request, TrainingObject $object)
    {

        foreach ($request->files as $file) {

            if (!is_iterable($file)) {
                $file_id = FileController::saveFile($file);

                $object->attachments()->create([
                    'file_id' => $file_id,
                    'hidden' => false // We hardcode this to false for now
                ]);
            } else {
                foreach ($file as $file2) {
                    $file_id = FileController::saveFile($file2);

                    $object->attachments()->create([
                        'file_id' => $file_id,
                        'hidden' => false // We hardcode this to false for now
                    ]);
                }
            }
        }

        return $object->fresh()->attachments()->pluck('id')->toArray();
    }
}
