<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Helpers\UploadFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateRequest;
use App\Http\Requests\Setting\UpdateImageRequest;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        return response()->json(['success' => true, 'setting' => $setting]);
    }

    public function update(UpdateRequest $request)
    {
        $data = $request->validated();

        Setting::first()->update($data);

        return response()->json(['success' => true]);
    }

    public function resetDefault(Request $request)
    {

        return response()->json(['success' => true]);
    }

    public function appImage()
    {
        $app = Setting::first();
        return view('backend.app_image.edit', compact('app'));
    }

    public function appImageUpdate(UpdateImageRequest $request)
    {
        $data = $request->validated();
        $app = Setting::first();
        if ($request->hasFile('image')) {
            if ($app->image) {
                $filePath = public_path("images/$app->image");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $uploadFile = new UploadFile();
            $imageName = $uploadFile->upload('images', $request->file('image'));
            $data['image'] = $imageName;
        }

        $app->update($data);

        return redirect()->route('admin.setting.appImage')->with('success', 'App Image Updated Successfully');
    }
}
