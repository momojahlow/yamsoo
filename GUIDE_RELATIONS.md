# Guide pour tester les Relations Familiales

## 🧹 Base de données nettoyée

La base de données a été nettoyée de toutes les relations pré-établies. Vous pouvez maintenant créer vos propres relations manuellement via l'interface.

## 👥 Utilisateurs disponibles

Voici les utilisateurs que vous pouvez utiliser pour tester (mot de passe : `password` pour tous) :

### Famille Benali
- **Ahmed Benali** : `ahmed.benali@example.com`
- **Fatima Zahra** : `fatima.zahra@example.com`
- **Amina Tazi** : `amina.tazi@example.com`

### Famille Alami
- **Mohammed Alami** : `mohammed.alami@example.com`
- **Leila Mansouri** : `leila.mansouri@example.com`

### Famille Bennani
- **Youssef Bennani** : `youssef.bennani@example.com`
- **Sara Benjelloun** : `sara.benjelloun@example.com`

### Famille Idrissi
- **Hassan Idrissi** : `hassan.idrissi@example.com`
- **Hanae Mernissi** : `hanae.mernissi@example.com`

### Autres utilisateurs
- **Karim El Fassi** : `karim.elfassi@example.com`
- **Omar Cherkaoui** : `omar.cherkaoui@example.com`
- **Nadia Berrada** : `nadia.berrada@example.com`
- **Zineb El Khayat** : `zineb.elkhayat@example.com`
- **Adil Benslimane** : `adil.benslimane@example.com`
- **Rachid Alaoui** : `rachid.alaoui@example.com`

## 🔗 Types de relations disponibles

- **Père** (father)
- **Mère** (mother)
- **Fils** (son)
- **Fille** (daughter)
- **Frère** (brother)
- **Sœur** (sister)
- **Mari** (husband)
- **Épouse** (wife)
- **Grand-père paternel** (grandfather_paternal)
- **Grand-mère paternelle** (grandmother_paternal)
- **Grand-père maternel** (grandfather_maternal)
- **Grand-mère maternelle** (grandmother_maternal)

## 🧪 Comment tester

### 1. Connexion
1. Allez sur `https://yamsoo.test/login`
2. Connectez-vous avec un des comptes ci-dessus
3. Mot de passe : `password`

### 2. Créer une demande de relation
1. Allez sur `/reseaux` ou `/family-relations`
2. Cliquez sur "Ajouter une relation"
3. Entrez l'email d'un autre utilisateur
4. Choisissez le type de relation
5. Ajoutez un message (optionnel)
6. Envoyez la demande

### 3. Accepter/Refuser des demandes
1. Connectez-vous avec le compte qui a reçu la demande
2. Allez sur `/reseaux` ou `/family-relations`
3. Vous verrez les demandes en attente
4. Acceptez ou refusez selon votre choix

### 4. Voir l'arbre familial
1. Une fois que vous avez des relations établies
2. Allez sur `/famille/arbre`
3. Visualisez votre arbre familial

## 🎯 Suggestions de test

### Scénario 1 : Couple
1. Connectez-vous avec `mohammed.alami@example.com`
2. Créez une demande de relation "épouse" vers `leila.mansouri@example.com`
3. Connectez-vous avec `leila.mansouri@example.com`
4. Acceptez la demande
5. Vérifiez que la relation apparaît des deux côtés

### Scénario 2 : Famille complète
1. Créez la relation Ahmed (père) ↔ Fatima (mère)
2. Créez Ahmed (père) ↔ Amina (fille)
3. Créez Fatima (mère) ↔ Amina (fille)
4. Visualisez l'arbre familial

### Scénario 3 : Frères et sœurs
1. Créez Karim (frère) ↔ Omar (frère)
2. Créez Nadia (sœur) ↔ Zineb (sœur)

## 🛠️ Commandes utiles

### Nettoyer complètement les relations
```bash
php artisan db:seed --class=FamilyRelationsSeeder
```

### Créer quelques demandes d'exemple (optionnel)
```bash
php artisan db:seed --class=SampleRelationRequestsSeeder
```

### Voir les logs en cas de problème
```bash
php artisan log:clear
tail -f storage/logs/laravel.log
```

## 📱 Pages à tester

- **Dashboard** : `/dashboard` - Vue d'ensemble avec cartes cliquables
- **Réseaux** : `/reseaux` - Gestion des relations familiales
- **Relations Familiales** : `/family-relations` - Page dédiée aux relations
- **Famille** : `/famille` - Liste des membres de famille
- **Arbre Familial** : `/famille/arbre` - Visualisation de l'arbre
- **Profil** : `/profil` - Gestion du profil personnel

Amusez-vous bien à tester ! 🎉
