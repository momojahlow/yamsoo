<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vérifiez votre adresse email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e9ecef;
        }
        .verify-icon {
            font-size: 48px;
            color: #6f42c1;
            text-align: center;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background: #6f42c1;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📧 Vérification d'email</h1>
    </div>
    
    <div class="content">
        <div class="verify-icon">✉️</div>
        
        <p>
            Bonjour <strong>{{ $user->name ?? 'Utilisateur' }}</strong>,
        </p>
        
        <p>
            Merci de vous être inscrit sur Yamsoo ! Pour finaliser la création de votre compte, nous devons vérifier votre adresse email.
        </p>
        
        <p>
            Cliquez sur le bouton ci-dessous pour confirmer votre adresse email :
        </p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationLink ?? '#' }}" class="button">
                Vérifier mon email
            </a>
        </div>
        
        <div class="info-box">
            <strong>ℹ️ Pourquoi vérifier votre email ?</strong>
            <ul>
                <li>Sécuriser votre compte</li>
                <li>Recevoir les notifications importantes</li>
                <li>Permettre à votre famille de vous retrouver</li>
                <li>Accéder à toutes les fonctionnalités de Yamsoo</li>
            </ul>
        </div>
        
        <p>
            Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
        </p>
        <p style="word-break: break-all; color: #007bff;">
            {{ $verificationLink ?? '' }}
        </p>
        
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            <strong>Vous n'avez pas créé de compte ?</strong><br>
            Si vous n'avez pas créé de compte sur Yamsoo, vous pouvez ignorer cet email en toute sécurité.
        </p>
    </div>
    
    <div class="footer">
        <p>
            Cet email a été envoyé automatiquement par <strong>Yamsoo</strong><br>
            L'application qui connecte les familles
        </p>
        <p>
            <a href="{{ config('app.url') }}">Accéder à Yamsoo</a>
        </p>
    </div>
</body>
</html>
