<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relation familiale acceptée</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .success-icon {
            font-size: 48px;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        .relationship-badge {
            background: #007bff;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .button {
            display: inline-block;
            background: #007bff;
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
        <h1>🎉 Relation familiale acceptée !</h1>
    </div>

    <div class="content">
        <div class="success-icon">✅</div>

        <p>
            Bonjour <strong>{{ $requester->name ?? 'Utilisateur' }}</strong>,
        </p>

        <p>
            Excellente nouvelle ! <strong>{{ $accepter->name ?? 'L\'utilisateur' }}</strong> a accepté votre demande de relation familiale.
        </p>

        <p>
            Vous êtes maintenant connectés en tant que :
            <span class="relationship-badge">{{ $relationshipType ?? 'Relation familiale' }}</span>
        </p>

        @if(isset($message) && $message)
        <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196f3;">
            <strong>Message :</strong><br>
            {{ $message }}
        </div>
        @endif

        <p>
            Vous pouvez maintenant :
        </p>
        <ul>
            <li>Voir votre relation dans votre réseau familial</li>
            <li>Échanger des messages</li>
            <li>Visualiser votre arbre familial mis à jour</li>
            <li>Partager des moments en famille</li>
        </ul>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/reseaux" class="button">
                Voir mes relations familiales
            </a>
        </div>
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
