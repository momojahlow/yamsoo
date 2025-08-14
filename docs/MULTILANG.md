# 🌍 Système Multi-langue Yamsoo

Ce document explique comment utiliser et étendre le système de multi-langue de Yamsoo qui supporte le **Français** (par défaut) et l'**Arabe**.

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Configuration](#configuration)
3. [Utilisation côté Frontend](#utilisation-côté-frontend)
4. [Utilisation côté Backend](#utilisation-côté-backend)
5. [Ajout de nouvelles langues](#ajout-de-nouvelles-langues)
6. [Composants disponibles](#composants-disponibles)
7. [Support RTL](#support-rtl)

## 🎯 Vue d'ensemble

Le système multi-langue de Yamsoo permet :
- **Changement dynamique** entre français et arabe
- **Support RTL** complet pour l'arabe
- **Persistance** des préférences utilisateur
- **Traductions** automatiques des interfaces
- **API simple** pour les développeurs

### Langues supportées
- 🇫🇷 **Français** (fr) - Langue par défaut
- 🇸🇦 **Arabe** (ar) - Support RTL complet

## ⚙️ Configuration

### Backend (Laravel)

#### 1. Configuration de base
```php
// config/app.php
'locale' => 'fr',
'fallback_locale' => 'fr',
'available_locales' => [
    'fr' => 'Français',
    'ar' => 'العربية',
],
```

#### 2. Middleware
Le middleware `SetLocale` gère automatiquement :
- Détection de la langue depuis l'URL
- Sauvegarde en session
- Préférences utilisateur

#### 3. Fichiers de traduction
```
lang/
├── fr/
│   ├── common.php
│   └── dashboard.php
└── ar/
    ├── common.php
    └── dashboard.php
```

## 🎨 Utilisation côté Frontend

### Hook useTranslation

```tsx
import { useTranslation } from '@/hooks/useTranslation';

function MyComponent() {
  const { t, currentLocale, isRTL, switchLanguage } = useTranslation();
  
  return (
    <div className={isRTL ? 'rtl' : 'ltr'}>
      <h1>{t('dashboard')}</h1>
      <p>{t('welcome')}</p>
      <button onClick={() => switchLanguage('ar')}>
        العربية
      </button>
    </div>
  );
}
```

### Fonctions disponibles

| Fonction | Description | Exemple |
|----------|-------------|---------|
| `t(key)` | Traduit une clé | `t('dashboard')` |
| `t(key, params)` | Traduit avec paramètres | `t('welcome_user', {name: 'Ahmed'})` |
| `switchLanguage(locale)` | Change la langue | `switchLanguage('ar')` |
| `currentLocale` | Langue actuelle | `'fr'` ou `'ar'` |
| `isRTL` | Direction RTL | `true` pour l'arabe |
| `getOppositeLanguage()` | Langue opposée | Pour toggle FR/AR |

## 🔧 Utilisation côté Backend

### Contrôleur de langue

```php
// Changer la langue
Route::get('/language/{locale}', [LanguageController::class, 'switch']);

// API des langues disponibles
Route::get('/api/languages', [LanguageController::class, 'getAvailableLanguages']);
```

### Dans les contrôleurs

```php
use Illuminate\Support\Facades\App;

class MyController extends Controller 
{
    public function index() 
    {
        $currentLang = App::getLocale(); // 'fr' ou 'ar'
        $translations = __('common'); // Charge les traductions
        
        return Inertia::render('MyPage', [
            'translations' => $translations,
            'locale' => $currentLang
        ]);
    }
}
```

## 🌐 Ajout de nouvelles langues

### 1. Ajouter la langue dans la configuration

```php
// config/app.php
'available_locales' => [
    'fr' => 'Français',
    'ar' => 'العربية',
    'en' => 'English', // Nouvelle langue
],
```

### 2. Créer les fichiers de traduction

```bash
mkdir lang/en
touch lang/en/common.php
touch lang/en/dashboard.php
```

### 3. Ajouter les traductions

```php
// lang/en/common.php
return [
    'dashboard' => 'Dashboard',
    'welcome' => 'Welcome to your family network',
    // ...
];
```

### 4. Mettre à jour les composants

Les composants `LanguageToggle` s'adapteront automatiquement aux nouvelles langues.

## 🎛️ Composants disponibles

### 1. LanguageToggle (Dropdown complet)

```tsx
import { LanguageToggle } from '@/components/LanguageToggle';

<LanguageToggle 
  variant="dropdown" 
  size="md" 
  showIcon={true} 
  showText={true} 
/>
```

### 2. QuickLanguageToggle (Toggle FR/AR)

```tsx
import { QuickLanguageToggle } from '@/components/LanguageToggle';

<QuickLanguageToggle className="ml-2" />
```

### 3. CurrentLanguageDisplay (Affichage seul)

```tsx
import { CurrentLanguageDisplay } from '@/components/LanguageToggle';

<CurrentLanguageDisplay className="text-sm" />
```

## 🔄 Support RTL

### CSS automatique

Le système applique automatiquement :
- `dir="rtl"` sur `<html>`
- Classes CSS RTL
- Police arabe
- Inversions de layout

### Classes RTL personnalisées

```css
/* Automatiquement appliqué pour l'arabe */
[dir="rtl"] .rtl\:flex-row-reverse {
    flex-direction: row-reverse;
}

[dir="rtl"] .rtl\:text-right {
    text-align: right;
}
```

### Dans les composants

```tsx
function MyComponent() {
  const { isRTL } = useTranslation();
  
  return (
    <div className={`flex ${isRTL ? 'flex-row-reverse' : ''}`}>
      <span className={isRTL ? 'mr-2' : 'ml-2'}>
        {t('text')}
      </span>
    </div>
  );
}
```

## 📝 Exemples pratiques

### Page avec traductions

```tsx
import { useTranslation } from '@/hooks/useTranslation';

export default function Dashboard() {
  const { t, isRTL } = useTranslation();
  
  return (
    <div className={`min-h-screen ${isRTL ? 'rtl' : 'ltr'}`}>
      <Head title={t('dashboard')} />
      
      <h1 className="text-2xl font-bold">
        {t('dashboard')}
      </h1>
      
      <p className="text-gray-600">
        {t('welcome')}
      </p>
      
      <div className={`flex gap-4 ${isRTL ? 'flex-row-reverse' : ''}`}>
        <button>{t('save')}</button>
        <button>{t('cancel')}</button>
      </div>
    </div>
  );
}
```

### Formulaire multilingue

```tsx
function ProfileForm() {
  const { t, isRTL } = useTranslation();
  
  return (
    <form className={isRTL ? 'rtl' : 'ltr'}>
      <label className={`block ${isRTL ? 'text-right' : 'text-left'}`}>
        {t('first_name')}
      </label>
      <input 
        type="text" 
        className={`w-full ${isRTL ? 'text-right' : 'text-left'}`}
        placeholder={t('first_name')}
      />
      
      <button type="submit">
        {t('save')}
      </button>
    </form>
  );
}
```

## 🚀 Bonnes pratiques

1. **Toujours utiliser `t()`** pour les textes affichés
2. **Tester en RTL** pour l'arabe
3. **Prévoir les textes longs** en arabe
4. **Utiliser les classes RTL** conditionnelles
5. **Maintenir la cohérence** des traductions

## 🔍 Débogage

### Vérifier la langue actuelle
```tsx
const { currentLocale } = useTranslation();
console.log('Langue actuelle:', currentLocale);
```

### Vérifier les traductions chargées
```tsx
const { t } = useTranslation();
console.log('Dashboard:', t('dashboard'));
```

### Forcer une langue
```tsx
// URL directe
window.location.href = '/language/ar';

// Ou via le hook
const { switchLanguage } = useTranslation();
switchLanguage('ar');
```

---

**🎉 Le système multi-langue Yamsoo est maintenant prêt à l'emploi !**
