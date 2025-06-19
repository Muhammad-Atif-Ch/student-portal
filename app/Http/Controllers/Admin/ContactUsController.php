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
}
