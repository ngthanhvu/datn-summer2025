<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Messenger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessengerController extends Controller
{
    public function getConversations()
    {
        $userId = Auth::id();
        $conversations = Messenger::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->get()
            ->map(function ($messenger) use ($userId) {
                $otherUserId = $messenger->user1_id == $userId ? $messenger->user2_id : $messenger->user1_id;
                $otherUser = User::find($otherUserId);
                $messages = is_array($messenger->messages) ? $messenger->messages : [];
                $latestMessage = end($messages) ?: null;
                $unreadCount = collect($messages)->where('sender_id', '!=', $userId)->where('is_read', false)->count();
                return [
                    'user' => $otherUser,
                    'latest_message' => $latestMessage,
                    'unread_count' => $unreadCount,
                    'messenger_id' => $messenger->id
                ];
            })->sortByDesc(function ($c) {
                return $c['latest_message']['sent_at'] ?? null;
            })->values();
        return response()->json($conversations);
    }

    public function getMessages($userId)
    {
        $currentUserId = Auth::id();
        $user1 = min($currentUserId, $userId);
        $user2 = max($currentUserId, $userId);
        $messenger = Messenger::where('user1_id', $user1)->where('user2_id', $user2)->first();
        if (!$messenger) {
            return response()->json([]);
        }
        $messages = is_array($messenger->messages) ? $messenger->messages : [];
        $updated = false;
        foreach ($messages as &$msg) {
            if ($msg['sender_id'] != $currentUserId && empty($msg['is_read'])) {
                $msg['is_read'] = true;
                $msg['read_at'] = now()->toDateTimeString();
                $updated = true;
            }
        }
        if ($updated) {
            $messenger->messages = $messages;
            $messenger->save();
        }
        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:10240'
        ]);
        $user1 = min(Auth::id(), $request->receiver_id);
        $user2 = max(Auth::id(), $request->receiver_id);
        $messenger = Messenger::firstOrCreate(
            ['user1_id' => $user1, 'user2_id' => $user2],
            ['messages' => []]
        );
        $messages = is_array($messenger->messages) ? $messenger->messages : [];
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }
        $newMessage = [
            'id' => uniqid(),
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'attachment' => $attachmentPath,
            'is_read' => false,
            'sent_at' => now()->toDateTimeString(),
            'read_at' => null
        ];
        $messages[] = $newMessage;
        $messenger->messages = $messages;
        $messenger->save();
        return response()->json($newMessage, 201);
    }

    public function markAsRead($messageId)
    {
        $userId = Auth::id();
        $messenger = Messenger::whereJsonContains('messages->*.id', $messageId)->first();
        if ($messenger) {
            $messages = is_array($messenger->messages) ? $messenger->messages : [];
            foreach ($messages as &$msg) {
                if ($msg['id'] == $messageId && $msg['sender_id'] != $userId) {
                    $msg['is_read'] = true;
                    $msg['read_at'] = now()->toDateTimeString();
                }
            }
            $messenger->messages = $messages;
            $messenger->save();
            return response()->json(['message' => 'Đã đánh dấu đã đọc']);
        }
        return response()->json(['error' => 'Không tìm thấy tin nhắn'], 404);
    }

    public function getUnreadCount()
    {
        $userId = Auth::id();
        $count = 0;
        $messengers = Messenger::where('user1_id', $userId)->orWhere('user2_id', $userId)->get();
        foreach ($messengers as $messenger) {
            $messages = is_array($messenger->messages) ? $messenger->messages : [];
            $count += collect($messages)->where('sender_id', '!=', $userId)->where('is_read', false)->count();
        }
        return response()->json(['unread_count' => $count]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $currentUserId = Auth::id();
        $users = User::where('id', '!=', $currentUserId)
            ->where(function ($q) use ($query) {
                $q->where('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'username', 'email', 'avatar')
            ->limit(10)
            ->get();
        return response()->json($users);
    }

    public function deleteMessage($messageId)
    {
        $userId = Auth::id();
        $messenger = Messenger::whereJsonContains('messages->*.id', $messageId)->first();
        if ($messenger) {
            $messages = is_array($messenger->messages) ? $messenger->messages : [];
            $messages = array_filter($messages, function ($msg) use ($messageId, $userId) {
                return !($msg['id'] == $messageId && $msg['sender_id'] == $userId);
            });
            $messenger->messages = array_values($messages);
            $messenger->save();
            return response()->json(['message' => 'Đã xóa tin nhắn']);
        }
        return response()->json(['error' => 'Không tìm thấy tin nhắn'], 404);
    }

    public function getAdmins()
    {
        $admins = User::where('role', 'admin')
            ->orWhere('role', 'master_admin')
            ->select('id', 'username', 'email', 'avatar', 'role')
            ->get();

        return response()->json($admins);
    }
}
