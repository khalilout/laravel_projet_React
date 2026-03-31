<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json(['user' => [
            'id' => $user->id,
            'username' => $user->username,
            'fullName' => $user->nom_complet,
            'createdAt' => $user->created_at,
        ]]);
    }

    public function friends($id)
    {
        $user = User::findOrFail($id);

        $friends = $user->allFriends()->map(function($f) {
            return [
                'id' => $f->id,
                'username' => $f->username,
                'fullName' => $f->nom_complet,
                'createdAt' => $f->created_at,
            ];
        });

        return response()->json(['friends' => $friends]);
    }
}
