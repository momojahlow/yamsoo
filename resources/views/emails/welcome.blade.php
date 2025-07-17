<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bienvenue sur Yamsoo</title>
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
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e9ecef;
        }
        .welcome-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 20px;
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
        .feature-list {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
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
        <h1>🎉 Bienvenue sur Yamsoo !</h1>
        <p>L'application qui connecte les familles</p>
    </div>
    
    <div class="content">
        <div class="welcome-icon">👨‍👩‍👧‍👦</div>
        
        <p>
            Bonjour <strong>{{ $user->name ?? 'Nouvel utilisateur' }}</strong>,
        </p>
        
        <p>
            Félicitations ! Votre compte Yamsoo a été créé avec succès. Vous faites maintenant partie d'une communauté qui valorise les liens familiaux et les connexions authentiques.
        </p>
        
        <div class="feature-list">
            <h3>🚀 Commencez dès maintenant :</h3>
            <ul>
                <li><strong>Complétez votre profil</strong> - Ajoutez vos informations personnelles</li>
                <li><strong>Trouvez votre famille</strong> - Recherchez et connectez-vous avec vos proches</li>
                <li><strong>Créez des relations</strong> - Établissez vos liens familiaux</li>
                <li><strong>Visualisez votre arbre</strong> - Découvrez votre arbre familial interactif</li>
                <li><strong>Échangez des messages</strong> - Communiquez avec votre famille</li>
            </ul>
        </div>
        
        <p>
            <strong>Votre voyage familial commence maintenant !</strong>
        </p>
        
        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/dashboard" class="button">
                Accéder à mon tableau de bord
            </a>
        </div>
        
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            <strong>Besoin d'aide ?</strong><br>
            Notre équipe est là pour vous accompagner. N'hésitez pas à nous contacter si vous avez des questions.
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
