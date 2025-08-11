<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SettingResource;
use App\Http\Requests\Api\UpdateSettingRequest;
use App\Models\Language;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $setting = User::with('membership')->where('device_id', $deviceId)->first();

        return new SettingResource($setting);
    }

    public function appImage()
    {
        $image = Setting::select('image')->first();

        return new SettingResource($image);
    }

    public function languages()
    {
        $languages = Language::where('show', '1')->get();

        return SettingResource::collection($languages);
    }

    public function update(UpdateSettingRequest $request)
    {
        $deviceId = $request->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $user->app_type = $request->app_type;
        $user->language_id = $request->language_id;
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return (new SettingResource($user))->additional([
            'message' => 'Setting updated successfully',
        ]);
    }
}
