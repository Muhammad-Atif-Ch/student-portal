<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadFile;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\UpdateClientAppRequest;
use App\Models\ClientApp;

class ClientAppController extends Controller
{
    public function index()
    {
        $clientApps = ClientApp::orderBy('id')->get();

        return view('backend.client_apps.index', compact('clientApps'));
    }

    public function update(UpdateClientAppRequest $request, ClientApp $clientApp)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($clientApp->image) {
                $filePath = public_path("images/{$clientApp->image}");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $uploadFile = new UploadFile;
            $data['image'] = $uploadFile->upload('images', $request->file('image'));
        }

        $clientApp->update($data);

        return redirect()->route('admin.client-apps.index')->with('success', 'App Updated Successfully');
    }
}
