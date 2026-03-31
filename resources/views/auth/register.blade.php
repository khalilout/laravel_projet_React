<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>

    <style>
        /* ===== DESIGN IDENTIQUE AU TIEN ===== */
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}

        body{
            min-height:100vh;
            background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }

        .particle{
            position:absolute;
            background:rgba(255,255,255,0.15);
            border-radius:50%;
            animation:float 15s infinite;
        }

        .particle:nth-child(1){width:90px;height:90px;top:15%;left:15%;}
        .particle:nth-child(2){width:70px;height:70px;top:70%;left:85%;}
        .particle:nth-child(3){width:110px;height:110px;top:85%;left:5%;}
        .particle:nth-child(4){width:60px;height:60px;top:25%;left:75%;}

        @keyframes float{
            0%,100%{transform:translate(0,0);}
            50%{transform:translate(-40px,-120px);}
        }

        .container{
            background:white;
            padding:45px;
            border-radius:25px;
            width:100%;
            max-width:480px;
            box-shadow:0 30px 80px rgba(0,0,0,0.3);
            z-index:10;
        }

        h1{text-align:center;margin-bottom:10px;}
        .subtitle{text-align:center;color:#777;margin-bottom:25px;}

        .input-group{margin-bottom:18px;}
        .input-group label{display:block;margin-bottom:6px;font-weight:500;}
        .input-group input{
            width:100%;
            padding:12px;
            border:2px solid #eee;
            border-radius:10px;
        }

        .input-group input:focus{
            border-color:#f093fb;
            outline:none;
        }

        .btn{
            width:100%;
            padding:14px;
            border:none;
            border-radius:10px;
            background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);
            color:white;
            font-weight:bold;
            cursor:pointer;
        }

        .btn:hover{transform:translateY(-2px);}

        .error{color:red;font-size:13px;margin-top:4px;}

        .login-link{
            text-align:center;
            margin-top:15px;
        }

        .login-link a{
            color:#f093fb;
            text-decoration:none;
        }
    </style>
</head>
<body>

<div class="particle"></div>
<div class="particle"></div>
<div class="particle"></div>
<div class="particle"></div>

<div class="container">

    <h1>Inscription</h1>
    <p class="subtitle">Créez votre compte MySocial</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- NOM COMPLET -->
        <div class="input-group">
            <label>Nom complet</label>
            <input type="text" id="nom_complet" name="nom_complet" value="{{ old('nom_complet') }}" required>

            @error('nom_complet')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- USERNAME -->
        <div class="input-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" value="{{ old('username') }}" required>

            @error('username')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- PASSWORD -->
        <div class="input-group">
            <label>Mot de passe</label>
            <input type="password" name="password" required>
        </div>

        <!-- CONFIRM PASSWORD -->
        <div class="input-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn">
            Créer mon compte
        </button>
    </form>

    <div class="login-link">
        Déjà un compte ?
        <a href="{{ route('login') }}">Se connecter</a>
    </div>

</div>

</body>
</html>