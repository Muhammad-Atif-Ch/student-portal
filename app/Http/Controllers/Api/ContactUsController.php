<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactUsRequest;
use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    public function index(ContactUsRequest $request)
    {
        $data = $request->validated();

        ContactUs::create($data);

        // Return the messages as a JSON response
        return response()->json(['data' => $data, 'success' => 'Data submit successfully'], 200);
    }
}
