<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateRequest;

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
}
