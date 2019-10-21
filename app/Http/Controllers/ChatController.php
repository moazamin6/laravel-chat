<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use App\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {

        $user = User::find($request->user['id']);
        broadcast(new ChatEvent($user, $request->message));
        return $request;
    }

    public function getAllUsers()
    {
        return User::where('id', '!=', auth()->id())->get();
    }
}
