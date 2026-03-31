<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Article;
use App\Models\Friend;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (auth()->attempt($credentials)) {
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'Nom d\'utilisateur Introuvable.',
        ]);
    }

    public function registerSubmit(Request $request)
    {
        $request->validate([
            'nom_complet' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'nom_complet' => $request->nom_complet,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);
        return redirect()->route('login');
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }

    public function dashboard()
    {
        $articles = Article::all();
        $user = auth()->user();

        // Demandes reçues
        $requests = Friend::where('friend_id', $user->id)
            ->where('status', 'pending')
            ->get();

        // Récupérer toutes les relations acceptées
        $friendRelations = Friend::where('status', 'accepted')
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhere('friend_id', $user->id);
            })->get();

        // Transformer en liste d'utilisateurs "amis"
        $friends = $friendRelations->map(function($f) use ($user) {
            // Si l'utilisateur connecté est user_id, l'ami est friend_id, sinon c'est user_id
            return $f->user_id == $user->id ? User::find($f->friend_id) : User::find($f->user_id);
        });

        return view('dashboard', compact('articles', 'user', 'friends', 'requests'));
    }
}
