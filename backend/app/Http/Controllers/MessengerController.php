<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Messenger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessengerController extends Controller
{
    // Lấy danh sách cuộc trò chuyện
    public function getConversations()
    {
        $userId = Auth::id();
        
        $conversations = Messenger::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('sent_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($userId) {
                return $message->sender_id == $userId ? $message->receiver_id : $message->sender_id;
            })
            ->map(function ($messages) use ($userId) {
                $latestMessage = $messages->first();
                $otherUser = $latestMessage->sender_id == $userId ? $latestMessage->receiver : $latestMessage->sender;
                $unreadCount = $messages->where('receiver_id', $userId)->where('is_read', false)->count();
                
                return [
                    'user' => $otherUser,
                    'latest_message' => $latestMessage,
                    'unread_count' => $unreadCount
                ];
            });

        return response()->json($conversations->values());
    }

    // Lấy tin nhắn giữa 2 người
    public function getMessages($userId)
    {
        $currentUserId = Auth::id();
        
        $messages = Messenger::where(function ($query) use ($currentUserId, $userId) {
            $query->where('sender_id', $currentUserId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($currentUserId, $userId) {
            $query->where('sender_id', $userId)->where('receiver_id', $currentUserId);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('sent_at', 'asc')
        ->get();

        // Đánh dấu tin nhắn đã đọc
        Messenger::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json($messages);
    }

    // Gửi tin nhắn
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:10240'
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $message = Messenger::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'attachment' => $attachmentPath,
            'sent_at' => now()
        ]);

        $message->load(['sender', 'receiver']);

        return response()->json($message, 201);
    }

    // Đánh dấu tin nhắn đã đọc
    public function markAsRead($messageId)
    {
        $message = Messenger::findOrFail($messageId);
        
        if ($message->receiver_id == Auth::id()) {
            $message->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return response()->json(['message' => 'Đã đánh dấu đã đọc']);
    }

    // Lấy số tin nhắn chưa đọc
    public function getUnreadCount()
    {
        $count = Messenger::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    // Tìm kiếm người dùng để chat
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $currentUserId = Auth::id();

        $users = User::where('id', '!=', $currentUserId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'username', 'email', 'avatar')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    // Xóa tin nhắn
    public function deleteMessage($messageId)
    {
        $message = Messenger::findOrFail($messageId);
        
        if ($message->sender_id == Auth::id()) {
            if ($message->attachment) {
                Storage::disk('public')->delete($message->attachment);
            }
            $message->delete();
            return response()->json(['message' => 'Đã xóa tin nhắn']);
        }

        return response()->json(['error' => 'Không có quyền xóa tin nhắn này'], 403);
    }

    // Lấy danh sách admin để chat
    public function getAdmins()
    {
        $admins = User::where('role', 'admin')
            ->select('id', 'username', 'email', 'avatar', 'role')
            ->get();

        return response()->json($admins);
    }
}
