<?php

namespace App\Http\Controllers;

use App\Mail\Contact;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index() {
        return view('contact');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ]);

        Mail::to('toneybrac@gmail.com')->send(new Contact($data));

        return redirect(route('contact.index'))->with('status', "Thank you, we'll be in touch soon");
    }
}
