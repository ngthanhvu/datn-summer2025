<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactReply;
use App\Mail\ContactDeleted;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string',
        ]);
        $contact = Contact::create($validated);
        return response()->json(['message' => 'Liên hệ của bạn đã được gửi thành công!'], 201);
    }

    public function reply(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $validated = $request->validate([
            'admin_reply' => 'required|string',
        ]);
        $contact->admin_reply = $validated['admin_reply'];
        $contact->replied_at = now();
        $contact->save();
        Mail::to($contact->email)->send(new ContactReply($contact, $validated['admin_reply']));
        return response()->json(['message' => 'Đã gửi phản hồi cho người dùng!']);
    }

    public function index()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->get();
        return response()->json($contacts);
    }

    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        return response()->json($contact);
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $hasReply = !empty($contact->admin_reply);
        $contact->delete();

        if (!$hasReply) {
            Mail::to($contact->email)->send(new ContactDeleted($contact));
        }
        return response()->json(['message' => 'Đã xóa liên hệ thành công!']);
    }
}
