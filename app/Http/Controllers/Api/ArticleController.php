<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Friend;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $friendIds = Friend::where('status', 'accepted')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            })
            ->get()
            ->map(function($f) use ($userId) {
                return $f->user_id == $userId ? $f->friend_id : $f->user_id;
            })->toArray();

        $articles = Article::where('user_id', $userId)
            ->orWhere(function($q) use ($friendIds) {
                $q->whereIn('user_id', $friendIds)
                  ->where('visibility', 'public');
            })
            ->with(['user','comments.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['articles' => $articles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visibility' => 'required|in:public,private',
        ]);

        $article = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'visibility' => $request->visibility,
            'comment_status' => $request->has('comment_status'),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['article' => $article], 201);
    }

    public function update(Request $request, Article $article)
    {
        if ($article->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

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

        return response()->json(['article' => $article]);
    }

    public function destroy(Article $article)
    {
        if ($article->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $article->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function comment(Request $request, Article $article)
    {
        $request->validate(['content' => 'required|string']);

        if (!$article->comment_status) {
            return response()->json(['message' => 'Comments disabled'], 400);
        }

        // Check visibility: owner or public or friend
        if ($article->user_id !== Auth::id()) {
            if ($article->visibility !== 'public') {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            // Prevent commenting if the article author has blocked the current user
            $blocked = \App\Models\Friend::where('status', 'blocked')
                ->where(function($q) use ($article) {
                    $q->where('user_id', $article->user_id)->where('friend_id', Auth::id());
                })->orWhere(function($q) use ($article) {
                    $q->where('user_id', Auth::id())->where('friend_id', $article->user_id);
                })->exists();

            if ($blocked) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $comment = $article->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json(['comment' => $comment], 201);
    }

    public function editComment(Request $request, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate(['content' => 'required|string']);

        $comment->update(['content' => $request->content]);
        return response()->json(['comment' => $comment]);
    }

    public function deleteComment(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
