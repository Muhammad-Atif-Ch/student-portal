<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactUsRequest;
use App\Models\ContactUs;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ContactUsController extends Controller
{
    public function index(ContactUsRequest $request)
    {
        try {
            $deviceId = request()->header('Device-Id');
            $user = User::where('device_id', $deviceId)->first();

            $data = $request->validated();
            $data['user_id'] = $user ? $user->id : null;

            ContactUs::create($data);

            Log::info('contact us data create successfully');
            // Return the messages as a JSON response
            return response()->json(['data' => $data, 'success' => 'Data submit successfully'], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            Log::error('Error submitting contact us form: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
