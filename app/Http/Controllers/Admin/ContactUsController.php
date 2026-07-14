<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Google\Service\AuthorizedBuyersMarketplace\Contact;

class ContactUsController extends Controller
{
    public function index()
    {
        $contactUs = ContactUs::with('user')->latest()->paginate(50);
        return view('backend.contact_us.index', compact('contactUs'));
    }

    public function updateStatus(Request $request, $id)
    {
        $contactUs = ContactUs::findOrFail($id); // Assuming you want to fetch all contact us messages
        $contactUs->status = $request->status; // Toggle status
        $contactUs->save();

        return response()->json(['success' => true, 'message' => 'Status Update successfully', 'status' => $contactUs->status]);
    }

    public function show(ContactUs $contact_us)
    {
        $contact_us->load('user');
        return view('backend.contact_us.show', compact('contact_us'));
    }
}
