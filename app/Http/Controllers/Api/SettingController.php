<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SettingResource;
use App\Http\Requests\Api\UpdateSettingRequest;
use App\Models\Lenguage;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $setting = User::where('device_id', $deviceId)->first();

        return new SettingResource($setting);
    }

    public function appImage()
    {
        $image = Setting::select('image')->first();

        return new SettingResource($image);
    }

    public function lenguages()
    {
        $lenguages = Lenguage::where('status', 'active')->get();

        return SettingResource::collection($lenguages);
    }

    public function update(UpdateSettingRequest $request)
    {
        $deviceId = $request->header('Device-Id');
        $setting = User::where('device_id', $deviceId)->first();
        $setting->app_type = $request->app_type;
        $setting->lenguage_id = $request->lenguage_id;
        $setting->save();

        return (new SettingResource($setting))->additional([
            'message' => 'Setting updated successfully',
        ]);
    }
}
