<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingRequest;
use App\Http\Resources\Api\SettingResource;
use App\Models\User;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $deviceId = $request->header('Device-Id');
        $setting = User::where('device_id', $deviceId)->first();

        return new SettingResource($setting);
    }

    public function update(UpdateSettingRequest $request)
    {
        $deviceId = $request->header('Device-Id');
        $setting = User::where('device_id', $deviceId)->first();
        $setting->app_type = $request->app_type;
        $setting->save();

        return (new SettingResource($setting))->additional([
            'message' => 'Setting updated successfully',
        ]);
    }
}
