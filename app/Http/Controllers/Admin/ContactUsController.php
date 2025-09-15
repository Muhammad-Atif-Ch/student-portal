<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactUsController extends Controller
{
    public function index()
    {
        $contactUs = ContactUs::all(); // Assuming you want to fetch all contact us messages

        return view('backend.contact_us.index', compact('contactUs')); // Adjust the view path as necessary
    }

    public function updateStatus(Request $request, $id)
    {
        $contactUs = ContactUs::findOrFail($id); // Assuming you want to fetch all contact us messages
        $contactUs->status = $request->status; // Toggle status
        $contactUs->save();

        return response()->json(['success' => true, 'message' => 'Status Update successfully', 'status' => $contactUs->status]);
    }
}
