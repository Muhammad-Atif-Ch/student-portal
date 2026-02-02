<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactUsRequest;

class ContactUsController extends Controller
{
    public function index(ContactUsRequest $request)
    {
        $deviceId = request()->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        
        $data = $request->validated();
        $data['user_id'] = $user ? $user->id : null;

        ContactUs::create($data);

        // Return the messages as a JSON response
        return response()->json(['data' => $data, 'success' => 'Data submit successfully'], 200);
    }
}
