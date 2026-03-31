<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ArticleController extends Controller
{
    use AuthorizesRequests;
    /**
     * Ajouter un article
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visibility' => 'required|in:public,private',
            'comment_status' => 'nullable|boolean',
        ]);

        Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'visibility' => $request->visibility,
            'comment_status' => $request->has('comment_status'),
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Article ajouté !');
    }

    /**
     * Modifier un article
     */
    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visibility' => 'required|in:public,private',
        ]);

        $article->update([
            'title' => $request->title,
            'content' => $request->content,
            'visibility' => $request->visibility,
            'comment_status' => $request->has('comment_status'),
        ]);

        return back()->with('success', 'Article modifié !');
    }

    /**
     * Supprimer un article
     */
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);
        $article->delete();
        return redirect()->back()->with('success', 'Article supprimé !');
    }

    /**
     * Ajouter un commentaire
     */
    public function comment(Request $request, Article $article)
    {
        $request->validate(['content' => 'required|string']);

        if (!$article->comment_status) {
            return back()->with('error', 'Les commentaires sont désactivés pour cet article.');
        }

        // Vérifier si l'utilisateur peut voir l'article
        if (!$this->canViewArticle($article)) {
            return back()->with('error', 'Vous ne pouvez pas commenter cet article.');
        }

        $article->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return back()->with('success', 'Commentaire ajouté !');
    }

    /**
     * Supprimer un commentaire
     */
    public function deleteComment(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return back()->with('success', 'Commentaire supprimé !');
    }

    /**
     * Modifier un commentaire
     */
    public function editComment(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate(['content' => 'required|string']);

        $comment->update(['content' => $request->content]);

        return back()->with('success', 'Commentaire modifié !');
    }

    /**
     * Dashboard : articles visibles, amis et demandes
     */
    public function index()
{
    $user = Auth::user();
    $userId = $user->id;

    // 🔹 Demandes reçues
    $requests = Friend::where('friend_id', $userId)
        ->where('status', 'pending')
        ->with('sender')
        ->get();

    // 🔹 Amis acceptés
    $friendIds = Friend::where('status', 'accepted')
        ->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('friend_id', $userId);
        })
        ->get()
        ->map(function($f) use ($userId) {
            return $f->user_id == $userId
                ? $f->friend_id
                : $f->user_id;
        })
        ->toArray();

    $friends = User::whereIn('id', $friendIds)->get();

    // 🔹 Articles visibles
    $articles = Article::where('user_id', $userId)
        ->orWhere(function($q) use ($friendIds) {
            $q->whereIn('user_id', $friendIds)
              ->where('visibility', 'public');
        })
        ->with(['user', 'comments.user'])
        ->orderBy('created_at', 'desc')
        ->get();

    // 🔹 Utilisateurs bloqués
    $blockedUsers = $user->blockedFriends();

    return view('dashboard', compact(
        'articles',
        'friends',
        'requests',
        'blockedUsers'
    ));
}

    /**
     * Vérifie si l'utilisateur peut voir un article (ami bilatéral ou propriétaire)
     */
    private function canViewArticle(Article $article)
    {
        $userId = Auth::id();

        if ($article->user_id == $userId) {
            return true; // propriétaire
        }

        // Vérifier relation acceptée bilatérale
        $friend = Friend::where('status', 'accepted')
            ->where(function ($q) use ($userId, $article) {
                $q->where('user_id', $userId)
                  ->where('friend_id', $article->user_id);
            })
            ->orWhere(function ($q) use ($userId, $article) {
                $q->where('user_id', $article->user_id)
                  ->where('friend_id', $userId);
            })
            ->first();

        return $friend && $article->visibility === 'public';
    }
}