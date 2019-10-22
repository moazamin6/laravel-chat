<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {

        $user = User::find($request->receiver_user['id']);
        $data = array(
            array(
                'message' => $request->message,
                'receiver_user' => $request->receiver_user,
                'sender_user' => $request->sender_user
            )
        );

        if (session()->has('chat')) {
            $session_data = session('chat');
        } else {
            $session_data = [];
        }

        $data = array_merge($session_data, $data);
        session(['chat' => $data]);
        broadcast(new ChatEvent($user, $request->message));
        return $request;
    }

    public function getAllUsers()
    {
        return User::where('id', '!=', auth()->id())->get();
    }

    public function getMessagesFromSession()
    {
        return session('chat');
    }
}
