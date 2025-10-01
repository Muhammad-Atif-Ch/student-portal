<?php

namespace App\Http\Controllers\Frontend;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactUsRequest;

class ContactUsController extends Controller
{
    public function index()
    {
        return view('frontend.contact-us');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        ContactUs::create($data);

        return redirect()->route('frontend.contact.index')->with('success', 'Contact Message created');
    }
}
