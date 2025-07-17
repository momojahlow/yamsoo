<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invitation à rejoindre une famille</title>
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
            background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
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
        .invitation-icon {
            font-size: 48px;
            color: #fd7e14;
            text-align: center;
            margin-bottom: 20px;
        }
        .family-badge {
            background: #fd7e14;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .button {
            display: inline-block;
            background: #fd7e14;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .inviter-info {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #fd7e14;
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
        <h1>🏠 Invitation familiale</h1>
    </div>
    
    <div class="content">
        <div class="invitation-icon">👨‍👩‍👧‍👦</div>
        
        <p>
            Bonjour,
        </p>
        
        <p>
            Vous avez été invité(e) à rejoindre une famille sur Yamsoo !
        </p>
        
        <div class="inviter-info">
            <strong>Invité par :</strong> {{ $inviter->name ?? 'Membre de famille' }}<br>
            <strong>Email :</strong> {{ $inviter->email ?? '' }}
        </div>
        
        <p>
            Famille : <span class="family-badge">{{ $familyName ?? 'Famille' }}</span>
        </p>
        
        <p>
            Yamsoo est l'application qui connecte les familles et vous permet de :
        </p>
        <ul>
            <li>Créer et visualiser votre arbre familial</li>
            <li>Échanger des messages avec vos proches</li>
            <li>Partager des moments précieux</li>
            <li>Organiser des événements familiaux</li>
            <li>Garder le contact avec toute la famille</li>
        </ul>
        
        <p>
            <strong>Rejoignez-nous dès maintenant !</strong>
        </p>
        
        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/register" class="button">
                Créer mon compte et rejoindre la famille
            </a>
        </div>
        
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            <strong>Déjà membre ?</strong><br>
            <a href="{{ config('app.url') }}/login">Connectez-vous à votre compte</a> pour accepter l'invitation.
        </p>
    </div>
    
    <div class="footer">
        <p>
            Cet email a été envoyé automatiquement par <strong>Yamsoo</strong><br>
            L'application qui connecte les familles
        </p>
        <p>
            <a href="{{ config('app.url') }}">Découvrir Yamsoo</a>
        </p>
    </div>
</body>
</html>
