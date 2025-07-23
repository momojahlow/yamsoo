# 🎯 Guide d'utilisation des Dropdowns Utilisateur

## 📋 Vue d'ensemble

Ce guide explique comment utiliser les différents composants de dropdown utilisateur disponibles dans l'application Yamsoo. Ces composants permettent d'afficher le nom de l'utilisateur connecté avec un menu déroulant contenant les options de profil et déconnexion.

## 🎨 Composants disponibles

### 1. **UserHeaderDropdown** - Composant principal
Composant polyvalent avec plusieurs variantes selon le contexte d'utilisation.

```tsx
import { UserHeaderDropdown } from '@/components/UserHeaderDropdown';

// Variante header (nom + avatar séparés)
<UserHeaderDropdown 
    user={auth.user} 
    variant="header" 
    showEmail={true}
    align="end"
/>

// Variante sidebar (nom + avatar ensemble)
<UserHeaderDropdown 
    user={auth.user} 
    variant="sidebar" 
    showEmail={true}
    align="end"
/>

// Variante compact (style bouton)
<UserHeaderDropdown 
    user={auth.user} 
    variant="compact" 
    showEmail={false}
    align="end"
/>
```

### 2. **UserNameDropdown** - Nom seul cliquable
Pour afficher uniquement le nom de l'utilisateur avec dropdown.

```tsx
import { UserNameDropdown } from '@/components/UserHeaderDropdown';

<UserNameDropdown 
    user={auth.user} 
    className="text-lg font-semibold"
/>
```

### 3. **UserAvatarDropdown** - Avatar seul cliquable
Pour afficher uniquement l'avatar avec dropdown.

```tsx
import { UserAvatarDropdown } from '@/components/UserHeaderDropdown';

<UserAvatarDropdown 
    user={auth.user} 
    size={8} // Taille de l'avatar
/>
```

## 🎯 Propriétés des composants

### UserHeaderDropdown Props
| Propriété | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `user` | `User` | - | Objet utilisateur (requis) |
| `variant` | `'header' \| 'sidebar' \| 'compact'` | `'header'` | Style du composant |
| `showEmail` | `boolean` | `true` | Afficher l'email sous le nom |
| `align` | `'start' \| 'center' \| 'end'` | `'end'` | Alignement du dropdown |

### UserNameDropdown Props
| Propriété | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `user` | `User` | - | Objet utilisateur (requis) |
| `className` | `string` | `''` | Classes CSS supplémentaires |

### UserAvatarDropdown Props
| Propriété | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `user` | `User` | - | Objet utilisateur (requis) |
| `size` | `number` | `8` | Taille de l'avatar (en unités Tailwind) |

## 🎨 Variantes visuelles

### Variante Header
- **Usage** : Headers principaux, barres de navigation
- **Apparence** : Nom + email à gauche, avatar à droite
- **Comportement** : Deux dropdowns séparés (nom et avatar)

### Variante Sidebar
- **Usage** : Sidebars, menus latéraux
- **Apparence** : Avatar + nom + email dans un seul élément
- **Comportement** : Un seul dropdown pour tout l'élément

### Variante Compact
- **Usage** : Espaces restreints, mobile
- **Apparence** : Avatar + prénom dans un bouton
- **Comportement** : Dropdown sur le bouton entier

## 🎯 Exemples d'utilisation

### Dans un Header
```tsx
export function AppHeader() {
    const { auth } = usePage<SharedData>().props;
    
    return (
        <header className="bg-white border-b">
            <div className="flex justify-between items-center px-6 py-4">
                <div className="flex items-center gap-4">
                    <h1>Mon App</h1>
                </div>
                
                <UserHeaderDropdown 
                    user={auth.user} 
                    variant="header"
                    showEmail={true}
                    align="end"
                />
            </div>
        </header>
    );
}
```

### Dans une Sidebar
```tsx
export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    
    return (
        <aside className="w-64 bg-gray-50">
            <div className="p-4">
                <UserHeaderDropdown 
                    user={auth.user} 
                    variant="sidebar"
                    showEmail={true}
                    align="start"
                />
            </div>
        </aside>
    );
}
```

### Dans un Header Mobile
```tsx
export function MobileHeader() {
    const { auth } = usePage<SharedData>().props;
    
    return (
        <header className="bg-white border-b md:hidden">
            <div className="flex justify-between items-center px-4 py-3">
                <h1>Mon App</h1>
                
                <UserHeaderDropdown 
                    user={auth.user} 
                    variant="compact"
                    showEmail={false}
                    align="end"
                />
            </div>
        </header>
    );
}
```

## 🎨 Personnalisation

### Styles CSS
Les composants utilisent les classes Tailwind CSS et peuvent être personnalisés :

```tsx
// Personnaliser les couleurs
<UserNameDropdown 
    user={auth.user}
    className="text-blue-600 hover:text-blue-800 font-bold"
/>

// Personnaliser la taille
<UserAvatarDropdown 
    user={auth.user}
    size={12} // Avatar plus grand
/>
```

### Menu personnalisé
Le contenu du dropdown est géré par le composant `UserMenuContent`. Pour le personnaliser :

```tsx
// Dans UserMenuContent.tsx
<DropdownMenuGroup>
    <DropdownMenuItem asChild>
        <Link href="/profile">
            <UserIcon className="mr-2 h-4 w-4" />
            Mon Profil
        </Link>
    </DropdownMenuItem>
    <DropdownMenuItem asChild>
        <Link href="/settings">
            <Settings className="mr-2 h-4 w-4" />
            Paramètres
        </Link>
    </DropdownMenuItem>
</DropdownMenuGroup>
```

## 🚀 Intégration dans l'application

### Étape 1: Importer le composant
```tsx
import { UserHeaderDropdown } from '@/components/UserHeaderDropdown';
```

### Étape 2: Récupérer les données utilisateur
```tsx
const { auth } = usePage<SharedData>().props;
```

### Étape 3: Utiliser le composant
```tsx
<UserHeaderDropdown 
    user={auth.user} 
    variant="header"
/>
```

## 🎯 Bonnes pratiques

1. **Choisir la bonne variante** selon le contexte
2. **Utiliser `align="end"`** pour les headers droits
3. **Utiliser `align="start"`** pour les sidebars gauches
4. **Masquer l'email** sur mobile avec `showEmail={false}`
5. **Tester sur différentes tailles d'écran**

## 🧪 Page de test

Une page de test est disponible à `/test-dropdown` pour voir tous les composants en action.

```bash
# Accéder à la page de test
http://localhost/test-dropdown
```

## 🔧 Dépannage

### Problème : Le dropdown ne s'affiche pas
- Vérifier que `UserMenuContent` est bien importé
- Vérifier que les données `auth.user` sont disponibles

### Problème : Styles incorrects
- Vérifier que Tailwind CSS est configuré
- Vérifier les classes CSS personnalisées

### Problème : Dropdown mal aligné
- Utiliser la prop `align` appropriée
- Ajuster selon la position dans la page

---

*Développé pour l'application Yamsoo - Connecter les familles du monde entier* 🌍
