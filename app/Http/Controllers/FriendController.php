<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    // Rechercher un utilisateur
    public function search(Request $request){
        $user = Auth::user();
        $userId = $user->id;

        $users = User::where('username', 'like', '%' . $request->username . '%')
            ->where('id', '!=', $userId)
            ->get();

        $requests = Friend::where('friend_id', $userId)
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        $friendIds = Friend::where('status', 'accepted')
            ->where(function($q) use ($userId){
                $q->where('user_id', $userId)
                ->orWhere('friend_id', $userId);
            })
            ->get()
            ->map(function($f) use ($userId){
                return $f->user_id == $userId
                    ? $f->friend_id
                    : $f->user_id;
            })
            ->toArray();

        $friends = User::whereIn('id', $friendIds)->get();

        $articles = Article::where('user_id', $userId)
            ->orWhere(function($q) use ($friendIds){
                $q->whereIn('user_id', $friendIds)
                ->where('visibility', 'public');
            })
            ->with(['user','comments.user'])
            ->latest()
            ->get();

        // 🔥 AJOUT IMPORTANT
        $blockedUsers = $user->blockedFriends();

        return view('dashboard', compact(
            'users',
            'requests',
            'friends',
            'articles',
            'blockedUsers'
        ));
    }

    // Envoyer une demande
    public function send($friendId){
        $userId = Auth::id();

        if ($userId == $friendId) {
            return back();
        }

        $existing = Friend::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)
            ->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)
            ->where('friend_id', $userId);
        })->first();

        if ($existing) {

            // Si c'était bloqué → on débloque et on remet en pending
            if ($existing->status == 'blocked') {
                $existing->update(['status' => 'pending']);
                return back()->with('success', 'Utilisateur débloqué et demande envoyée.');
            }

            return back()->with('error', 'Relation déjà existante.');
        }

        Friend::create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Demande envoyée.');
    }

    // Accepter une demande
    public function accept($id)
    {
        $request = Friend::findOrFail($id);
        $request->update(['status' => 'accepted']);
        return redirect()->back();
    }

    // Refuser ou bloquer
    public function reject($id)
    {
        $request = Friend::findOrFail($id);
        $request->delete(); // ou update status en 'blocked'
        return redirect()->back();
    }

    // Supprimer un ami
    public function remove($friendId)
    {
        Friend::where(function($q) use($friendId){
            $q->where('user_id', Auth::id())->where('friend_id', $friendId);
        })->orWhere(function($q) use($friendId){
            $q->where('user_id', $friendId)->where('friend_id', Auth::id());
        })->delete();

        return redirect()->back();
    }

    //bloquer un utilisateur
    public function block($id){
        $userId = Auth::id();

        $friendship = Friend::where(function($q) use ($id, $userId){
            $q->where('user_id', $userId)
            ->where('friend_id', $id);
        })->orWhere(function($q) use ($id, $userId){
            $q->where('user_id', $id)
            ->where('friend_id', $userId);
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

        return back()->with('success','Utilisateur bloqué.');
    }

    //débloquer un utilisateur
    public function unblock($id){
        $friendship = \App\Models\Friend::where(function($q) use ($id){
            $q->where('user_id', Auth::id())
            ->where('friend_id', $id);
        })->orWhere(function($q) use ($id){
            $q->where('user_id', $id)
            ->where('friend_id', Auth::id());
        })->first();

        if($friendship){
            $friendship->update(['status' => 'accepted']);
        }

        return back()->with('success','Utilisateur débloqué.');
    }

    // public function blockedFriends(){
    //     $blockedIds = Friend::where('status', 'blocked')
    //         ->where(function($q) {
    //             $q->where('user_id', Auth::id())
    //             ->orWhere('friend_id', Auth::id());
    //         })
    //         ->get()
    //         ->map(function($f) {
    //             // Retourne l'autre utilisateur que soi-même
    //             return $f->user_id == $this->id ? $f->friend_id : $f->user_id;
    //         })->toArray();

    //     return User::whereIn('id', $blockedIds)->get();
    // }
}