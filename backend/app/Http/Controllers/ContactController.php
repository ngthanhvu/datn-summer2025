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

        // Gửi mail bất đồng bộ
        Mail::to($contact->email)->queue(new ContactReply($contact, $validated['admin_reply']));

        return response()->json(['message' => 'Đã gửi phản hồi cho người dùng!']);
    }

    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->has('search') && $request->search && trim($request->search) !== '') {
            $query->search(trim($request->search));
        }

        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'replied':
                    $query->whereNotNull('admin_reply');
                    break;
                case 'unreplied':
                    $query->whereNull('admin_reply');
                    break;
                default:
                    break;
            }
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['created_at', 'name', 'email'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $contacts = $query->paginate($perPage);

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
            // Gửi mail bất đồng bộ
            Mail::to($contact->email)->queue(new ContactDeleted($contact));
        }

        return response()->json(['message' => 'Đã xóa liên hệ thành công!']);
    }

    public function stats()
    {
        $stats = [
            'total' => Contact::count(),
            'replied' => Contact::whereNotNull('admin_reply')->count(),
            'unreplied' => Contact::whereNull('admin_reply')->count()
        ];

        return response()->json($stats);
    }
}
