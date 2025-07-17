<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nouveau message familial</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .message-icon {
            font-size: 48px;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        .sender-info {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .button {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
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
        <h1>💬 Nouveau message familial</h1>
    </div>
    
    <div class="content">
        <div class="message-icon">📨</div>
        
        <p>
            Bonjour <strong>{{ $recipient->name ?? 'Utilisateur' }}</strong>,
        </p>
        
        <p>
            Vous avez reçu un nouveau message de la part d'un membre de votre famille !
        </p>
        
        <div class="sender-info">
            <strong>Message de :</strong> {{ $sender->name ?? 'Membre de famille' }}<br>
            <strong>Email :</strong> {{ $sender->email ?? '' }}
        </div>
        
        <p>
            Connectez-vous à votre compte Yamsoo pour lire le message complet et répondre à votre proche.
        </p>
        
        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/messagerie" class="button">
                Lire mes messages
            </a>
        </div>
        
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            <strong>Conseil :</strong> Gardez le contact avec votre famille grâce à Yamsoo. Les petits messages font les grandes relations !
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
