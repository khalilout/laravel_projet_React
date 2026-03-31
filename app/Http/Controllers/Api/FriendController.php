<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    public function search(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $users = User::where('username', 'like', '%' . $request->username . '%')
            ->where('id', '!=', $userId)
            ->get();

        return response()->json(['users' => $users]);
    }

    public function send($friendId)
    {
        $userId = Auth::id();

        if ($userId == $friendId) {
            return response()->json(['message' => 'Cannot friend yourself'], 400);
        }

        $existing = Friend::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $userId);
        })->first();

        if ($existing) {
            if ($existing->status == 'blocked') {
                $existing->update(['status' => 'pending']);
                return response()->json(['message' => 'Unblocked and request sent']);
            }
            return response()->json(['message' => 'Relation exists'], 400);
        }

        Friend::create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Request sent']);
    }

    public function accept($id)
    {
        $request = Friend::findOrFail($id);
        $request->update(['status' => 'accepted']);
        return response()->json(['message' => 'Accepted']);
    }

    public function reject($id)
    {
        $request = Friend::findOrFail($id);
        $request->delete();
        return response()->json(['message' => 'Rejected']);
    }

    public function remove($friendId)
    {
        Friend::where(function($q) use($friendId){
            $q->where('user_id', Auth::id())->where('friend_id', $friendId);
        })->orWhere(function($q) use($friendId){
            $q->where('user_id', $friendId)->where('friend_id', Auth::id());
        })->delete();

        return response()->json(['message' => 'Removed']);
    }

    public function block($id)
    {
        $userId = Auth::id();

        $friendship = Friend::where(function($q) use ($id, $userId){
            $q->where('user_id', $userId)->where('friend_id', $id);
        })->orWhere(function($q) use ($id, $userId){
            $q->where('user_id', $id)->where('friend_id', $userId);
        })->first();

        if($friendship){
            $friendship->update(['status' => 'blocked']);
        } else {
            Friend::create([
                'user_id' => $userId,
                'friend_id' => $id,
                'status' => 'blocked'
            ]);
        }

        return response()->json(['message' => 'Blocked']);
    }

    public function unblock($id)
    {
        $friendship = Friend::where(function($q) use ($id){
            $q->where('user_id', Auth::id())->where('friend_id', $id);
        })->orWhere(function($q) use ($id){
            $q->where('user_id', $id)->where('friend_id', Auth::id());
        })->first();

        if($friendship){
            $friendship->update(['status' => 'accepted']);
        }

        return response()->json(['message' => 'Unblocked']);
    }

    // Liste les utilisateurs bloqués par ou ayant bloqué l'utilisateur connecté
    public function blocked()
    {
        $userId = Auth::id();

        $blockedIds = Friend::where('status', 'blocked')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            })
            ->get()
            ->map(function($f) use ($userId) {
                return $f->user_id == $userId ? $f->friend_id : $f->user_id;
            })->toArray();

        $users = User::whereIn('id', $blockedIds)->get();
        return response()->json(['blocked' => $users]);
    }
}
