<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de votre mot de passe</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
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
        .security-icon {
            font-size: 48px;
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
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
        <h1>🔐 Réinitialisation de mot de passe</h1>
    </div>
    
    <div class="content">
        <div class="security-icon">🔑</div>
        
        <p>
            Bonjour <strong>{{ $user->name ?? 'Utilisateur' }}</strong>,
        </p>
        
        <p>
            Vous avez demandé la réinitialisation de votre mot de passe pour votre compte Yamsoo.
        </p>
        
        <p>
            Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :
        </p>
        
        <div style="text-align: center;">
            <a href="{{ $resetLink ?? '#' }}" class="button">
                Réinitialiser mon mot de passe
            </a>
        </div>
        
        <div class="warning-box">
            <strong>⚠️ Important :</strong>
            <ul>
                <li>Ce lien est valide pendant 60 minutes seulement</li>
                <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                <li>Votre mot de passe actuel reste inchangé jusqu'à ce que vous en créiez un nouveau</li>
            </ul>
        </div>
        
        <p>
            Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
        </p>
        <p style="word-break: break-all; color: #007bff;">
            {{ $resetLink ?? '' }}
        </p>
        
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            <strong>Besoin d'aide ?</strong><br>
            Si vous rencontrez des difficultés, contactez notre support.
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
