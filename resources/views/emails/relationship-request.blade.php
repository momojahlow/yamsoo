<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nouvelle demande de relation familiale</title>
</head>
<body>
    <h2>Nouvelle demande de relation familiale</h2>
    <p>
        Bonjour {{ $target->name ?? '' }},
    </p>
    <p>
        {{ $requester->name ?? 'Un utilisateur' }} souhaite vous ajouter en tant que <strong>{{ $relationshipType ?? 'relation' }}</strong> sur Yamsoo.
    </p>
    <p>
        Connectez-vous à votre compte pour accepter ou refuser cette demande.
    </p>
</body>
</html>
