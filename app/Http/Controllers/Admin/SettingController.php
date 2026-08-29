<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateApiSettingsRequest;
use App\Http\Requests\Setting\UpdateRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
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

    public function apiSettings()
    {
        $setting = Setting::first();

        return view('backend.api_settings.edit', compact('setting'));
    }

    public function apiSettingsUpdate(UpdateApiSettingsRequest $request)
    {
        $data = $request->validated();
        Setting::first()->update($data);
        Cache::forget('app_settings');

        return redirect()->route('admin.setting.apiSettings')->with('success', 'API Settings Updated Successfully');
    }
}
