# 🌱 Seeders de Base de Données - Yamsoo

Ce document explique comment utiliser les seeders pour peupler la base de données avec des données de test réalistes.

## 📋 Contenu des Seeders

### 1. **RelationshipTypesSeeder**
- Types de relations familiales (père, mère, fils, fille, frère, sœur, etc.)
- Support multilingue (français, arabe, anglais)
- Configuration des relations qui nécessitent le nom de la mère

### 2. **UsersSeeder**
- 15 utilisateurs avec profils complets
- Noms marocains réalistes
- Profils avec bio, localisation, date de naissance, genre, téléphone
- Mots de passe : `password` pour tous les comptes

### 3. **FamilyRelationsSeeder**
- Relations familiales existantes entre utilisateurs
- Familles complètes (mari/femme/enfant)
- Relations fraternelles
- Demandes de relation en attente

### 4. **NotificationsSeeder**
- Notifications réalistes pour chaque utilisateur
- Types variés : demandes de relation, anniversaires, événements, etc.
- Certaines lues, d'autres non lues

### 5. **SuggestionsSeeder**
- Suggestions de relations familiales
- Scores de confiance variés
- Raisons détaillées pour chaque suggestion

## 🚀 Comment utiliser les seeders

### Option 1 : Script automatique (Linux/Mac)
```bash
./seed-database.sh
```

### Option 2 : Commandes manuelles
```bash
# Vider et recréer la base de données
php artisan migrate:fresh

# Exécuter tous les seeders
php artisan db:seed

# Ou exécuter un seeder spécifique
php artisan db:seed --class=UsersSeeder
```

### Option 3 : Windows PowerShell
```powershell
# Vider et recréer la base de données
php artisan migrate:fresh

# Exécuter tous les seeders
php artisan db:seed
```

## 👥 Comptes de Test Disponibles

### 👤 Utilisateur de Test
- **Email** : `test@example.com`
- **Mot de passe** : `password`

### 👨‍👩‍👧‍👦 Famille Benali
- **Ahmed Benali** : `ahmed.benali@example.com` / `password`
- **Fatima Zahra** : `fatima.zahra@example.com` / `password`
- **Amina Tazi** : `amina.tazi@example.com` / `password`

### 💑 Couples
- **Mohammed Alami** : `mohammed.alami@example.com` / `password`
- **Leila Mansouri** : `leila.mansouri@example.com` / `password`
- **Youssef Bennani** : `youssef.bennani@example.com` / `password`
- **Sara Benjelloun** : `sara.benjelloun@example.com` / `password`
- **Hassan Idrissi** : `hassan.idrissi@example.com` / `password`
- **Hanae Mernissi** : `hanae.mernissi@example.com` / `password`

### 👥 Autres Utilisateurs
- **Karim El Fassi** : `karim.elfassi@example.com` / `password`
- **Omar Cherkaoui** : `omar.cherkaoui@example.com` / `password`
- **Nadia Berrada** : `nadia.berrada@example.com` / `password`
- **Zineb El Khayat** : `zineb.elkhayat@example.com` / `password`
- **Adil Benslimane** : `adil.benslimane@example.com` / `password`
- **Rachid Alaoui** : `rachid.alaoui@example.com` / `password`

## 🏗️ Structure des Données

### Relations Familiales Existantes
- **Famille Benali** : Ahmed (père) + Fatima (mère) + Amina (fille)
- **Famille Alami** : Mohammed + Leila (couple)
- **Famille Bennani** : Youssef + Sara (couple)
- **Famille Idrissi** : Hassan + Hanae (couple)
- **Relations fraternelles** : Karim ↔ Omar, Nadia ↔ Zineb

### Demandes en Attente
- Adil → Rachid (demande de relation fraternelle)
- Rachid → Adil (demande de relation fraternelle)

### Notifications
- Chaque utilisateur a 1-2 notifications
- Types variés : demandes, anniversaires, événements, etc.

### Suggestions
- Suggestions de relations cousines entre utilisateurs
- Scores de confiance de 65% à 90%
- Raisons basées sur noms, régions, professions, etc.

## 🧪 Tests Recommandés

1. **Connexion** : Testez avec différents comptes
2. **Page Réseaux** : Vérifiez l'affichage des utilisateurs et relations
3. **Demandes de relation** : Testez l'envoi et la réception
4. **Notifications** : Vérifiez l'affichage des notifications
5. **Suggestions** : Testez les suggestions de relations
6. **Arbre généalogique** : Vérifiez l'affichage des relations familiales

## 🔧 Personnalisation

Pour modifier les données, éditez les fichiers seeders dans `database/seeders/` :

- `UsersSeeder.php` : Ajouter/modifier des utilisateurs
- `FamilyRelationsSeeder.php` : Modifier les relations familiales
- `NotificationsSeeder.php` : Ajouter des notifications
- `SuggestionsSeeder.php` : Modifier les suggestions

## 📝 Notes

- Tous les mots de passe sont `password`
- Les emails sont fictifs (domaine `@example.com`)
- Les données sont réalistes mais fictives
- Les relations respectent la logique familiale
- Les notifications ont des dates variées (récentes et anciennes) 
