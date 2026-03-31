<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{min-height:100vh;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px;overflow:auto;}
.particle{position:absolute;background:rgba(255,255,255,0.1);border-radius:50%;animation:float 18s infinite;}
.particle:nth-child(1){width:120px;height:120px;top:10%;left:10%;}
.particle:nth-child(2){width:80px;height:80px;top:70%;left:85%;}
.particle:nth-child(3){width:100px;height:100px;top:80%;left:5%;}
@keyframes float{0%,100%{transform:translate(0,0);}50%{transform:translate(-60px,-100px);}}

/* Styles généraux */
.header{color:white;margin-bottom:30px;}
.section{margin-bottom:40px;}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:25px;}
.card{background:white;padding:25px;border-radius:20px;box-shadow:0 15px 40px rgba(0,0,0,0.2);transition:all 0.3s ease;}
.card:hover{transform:translateY(-5px);}
.badge{display:inline-block;padding:4px 10px;border-radius:15px;font-size:12px;margin-bottom:10px;}
.public{background:#d1fae5;color:#065f46;}
.private{background:#fee2e2;color:#991b1b;}
.btn{padding:6px 12px;border:none;border-radius:8px;cursor:pointer;margin-top:10px;}
.add{background:#667eea;color:white;}
.accept{background:#10b981;color:white;}
.reject{background:#ef4444;color:white;}
.remove{background:#f59e0b;color:white;}
.block{background:#ef4444;color:white;}
.unblock{background:#10b981;color:white;}
.search-box{margin-bottom:20px;}
input, textarea, select{padding:10px;border-radius:10px;border:none;width:100%;}
.logout{margin-top:30px;display:inline-block;padding:10px 20px;background:white;border-radius:10px;text-decoration:none;color:#764ba2;font-weight:bold;}
.comment-box{margin-top:10px;}
.comment-box p{margin-bottom:5px;font-size:14px;}
.comment-box strong{color:#333;}
</style>
</head>
<body>

<div class="particle"></div>
<div class="particle"></div>
<div class="particle"></div>

<div class="header">
    <h1>Bienvenue {{ auth()->user()->username }}</h1>
</div>

{{-- 🔎 RECHERCHE UTILISATEUR --}}
<div class="section">
    <h2 style="color:white;margin-bottom:15px;">Rechercher un ami</h2>
    <form method="GET" action="{{ route('search.user') }}" class="search-box">
        <input type="text" name="username" placeholder="Nom d'utilisateur...">
        <button class="btn add">Rechercher</button>
    </form>

    @if(isset($users))
        @foreach($users as $user)
            <div class="card">
                <h3>{{ $user->username }}</h3>
                <form method="POST" action="{{ route('friend.send', $user->id) }}">
                    @csrf
                    <button class="btn add">Envoyer demande</button>
                </form>
            </div>
        @endforeach
    @endif
</div>

{{-- 📩 DEMANDES REÇUES --}}
<div class="section">
    <h2 style="color:white;margin-bottom:15px;">Demandes reçues</h2>
    @isset($requests)
        @foreach($requests as $request)
            <div class="card">
                <h3>{{ $request->sender->username }}</h3>
                <form method="POST" action="{{ route('friend.accept', $request->id) }}">
                    @csrf
                    <button class="btn accept">Accepter</button>
                </form>
                <form method="POST" action="{{ route('friend.reject', $request->id) }}">
                    @csrf
                    <button class="btn reject">Refuser</button>
                </form>
            </div>
        @endforeach
    @endisset
</div>

{{-- 👥 LISTE AMIS --}}
<div class="section">
    <h2 style="color:white;margin-bottom:15px;">Mes amis</h2>
    @isset($friends)
        @foreach($friends as $friend)
            <div class="card">
                <h3>{{ $friend->username }}</h3>

                {{-- Supprimer --}}
                <form method="POST" action="{{ route('friend.remove', $friend->id) }}">
                    @csrf
                    <button class="btn remove">Supprimer</button>
                </form>

                {{-- Bloquer / Débloquer --}}
                @php
                    $friendship = \App\Models\Friend::where(function($q) use ($friend) {
                        $q->where('user_id', auth()->id())->where('friend_id', $friend->id);
                    })->orWhere(function($q) use ($friend) {
                        $q->where('user_id', $friend->id)->where('friend_id', auth()->id());
                    })->first();
                @endphp

                @if($friendship && $friendship->status === 'blocked')
                    <form method="POST" action="{{ route('friends.unblock', $friend->id) }}">
                        @csrf
                        <button class="btn unblock">Débloquer</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('friend.block', $friend->id) }}">
                        @csrf
                        <button class="btn block">Bloquer</button>
                    </form>
                @endif
            </div>
        @endforeach
    @endisset
</div>

{{-- 🔒 UTILISATEURS BLOQUÉS --}}
<div class="section">
    <h2 style="color:white;margin-bottom:15px;">Utilisateurs bloqués</h2>

    @if(isset($blockedUsers) && $blockedUsers->count() > 0)
        @foreach($blockedUsers as $blocked)
            <div class="card">
                <h3>{{ $blocked->username }}</h3>
                <form method="POST" action="{{ route('friends.unblock', $blocked->id) }}">
                    @csrf
                    <button class="btn unblock">Débloquer</button>
                </form>
            </div>
        @endforeach
    @else
        <div class="card">
            <p>Aucun utilisateur bloqué.</p>
        </div>
    @endif
    
</div>

{{-- 📝 AJOUT / MODIFICATION D'ARTICLE --}}
<div class="section">
    <h2 style="color:white;margin-bottom:15px;">Ajouter un article</h2>
    <form method="POST" action="{{ route('article.store') }}">
        @csrf
        <input type="text" name="title" placeholder="Titre de l'article" required>
        <textarea name="content" placeholder="Contenu..." rows="4" required></textarea>
        <select name="visibility" required>
            <option value="public">Public</option>
            <option value="private">Privé</option>
        </select>
        <label>
            <input type="checkbox" name="comment_status" value="1"> Autoriser les commentaires
        </label>
        <button class="btn add">Publier</button>
    </form>
</div>

{{-- 📰 ARTICLES --}}
<div class="section">
    <h2 style="color:white;margin-bottom:15px;">Articles</h2>
    <div class="cards">
        @php
            $friendIds = $friends->pluck('id')->toArray();
        @endphp

        @foreach($articles as $article)
            @php
                $isOwner = $article->user_id === auth()->id();
                $isFriend = in_array($article->user_id, $friendIds);
                $canView = $isOwner || ($article->visibility === 'public' && $isFriend);
            @endphp

            @if($canView)
                <div class="card">
                    <span class="badge {{ $article->visibility }}">{{ ucfirst($article->visibility) }}</span>
                    <h3>{{ $article->title }}</h3>
                    <p>{{ $article->content }}</p>
                    <small>Par {{ $article->user->username }}</small>

                    {{-- Modifier / Supprimer si propriétaire --}}
                    @if($isOwner)
                        <form method="POST" action="{{ route('article.update', $article->id) }}">
                            @csrf
                            @method('PUT')
                            <input type="text" name="title" value="{{ $article->title }}" required>
                            <textarea name="content" required>{{ $article->content }}</textarea>
                            <select name="visibility">
                                <option value="public" {{ $article->visibility == 'public' ? 'selected' : '' }}>Public</option>
                                <option value="private" {{ $article->visibility == 'private' ? 'selected' : '' }}>Privé</option>
                            </select>
                            <label>
                                <input type="checkbox" name="comment_status" value="1" {{ $article->comment_status ? 'checked' : '' }}> Autoriser les commentaires
                            </label>
                            <button class="btn add">Modifier</button>
                        </form>

                        <form method="POST" action="{{ route('article.destroy', $article->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn reject">Supprimer</button>
                        </form>
                    @endif

                    {{-- Commentaires --}}
                    @if($article->comment_status)
                        <form method="POST" action="{{ route('article.comment', $article->id) }}" class="comment-box">
                            @csrf
                            <textarea name="content" placeholder="Écrire un commentaire..." rows="2" required></textarea>
                            <button type="submit" class="btn add">Commenter</button>
                        </form>
                    @endif

                    @foreach($article->comments as $comment)
                        <div class="comment-box">
                            <p><strong>{{ $comment->user->username }}:</strong> {{ $comment->content }}</p>
                            @if($comment->user_id == auth()->id())
                                <form method="POST" action="{{ route('article.comment.update', $comment->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="content" value="{{ $comment->content }}" required>
                                    <button class="btn add">Modifier</button>
                                </form>

                                <form method="POST" action="{{ route('article.comment.delete', $comment->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn reject">Supprimer</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button class="logout">Se déconnecter</button>
</form>

</body>
</html>