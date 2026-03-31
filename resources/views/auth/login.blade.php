<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>

    <style>
        /* ====== TON STYLE ORIGINAL (inchangé) ====== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        .particle:nth-child(1) { width:80px;height:80px;top:10%;left:20%; }
        .particle:nth-child(2) { width:60px;height:60px;top:60%;left:80%; }
        .particle:nth-child(3) { width:100px;height:100px;top:80%;left:10%; }
        .particle:nth-child(4) { width:50px;height:50px;top:30%;left:70%; }

        @keyframes float {
            0%,100% { transform: translate(0,0); opacity:0.3; }
            50% { transform: translate(-30px,-80px); opacity:0.6; }
        }

        .login-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            padding: 50px 40px;
            position: relative;
            z-index: 10;
        }

        h1 {
            font-size: 28px;
            color: #2d3748;
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f7fafc;
        }

        .input-group input:focus {
            border-color: #667eea;
            outline: none;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
        }

        .signup-link a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="particle"></div>
<div class="particle"></div>
<div class="particle"></div>
<div class="particle"></div>

<div class="login-container">

    <h1>Connexion</h1>
    <p class="subtitle">Bienvenue sur MySocial</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

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

            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- REMEMBER -->
        <div style="margin-bottom:15px;">
            <label>
                <input type="checkbox" name="remember">
                Se souvenir de moi
            </label>
        </div>

        <button type="submit" class="btn-login">
            Se connecter
        </button>
    </form>

    <div class="signup-link">
        Pas encore inscrit ?
        <a href="{{ route('register') }}">Créer un compte</a>
    </div>

</div>

</body>
</html>