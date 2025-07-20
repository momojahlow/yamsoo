<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'admin';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Vérifier si l'admin a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifier si l'admin a l'une des permissions spécifiées
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Vérifier si l'admin a l'une des permissions spécifiées
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->permissions ?? []));
    }

    /**
     * Vérifier si l'admin est super administrateur
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Obtenir le nom du rôle en français
     */
    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrateur',
            'super_admin' => 'Super Administrateur',
            'moderator' => 'Modérateur',
            default => 'Inconnu',
        };
    }

    /**
     * Enregistrer la dernière connexion
     */
    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Activer/désactiver l'admin
     */
    public function setActive(bool $active): void
    {
        $this->update(['is_active' => $active]);
    }

    /**
     * Ajouter une permission
     */
    public function givePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Retirer une permission
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Scope pour les admins actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Logs d'activité de l'admin
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    /**
     * Permissions disponibles dans le système
     */
    public static function getAvailablePermissions(): array
    {
        return [
            'users.view' => 'Voir les utilisateurs',
            'users.create' => 'Créer des utilisateurs',
            'users.edit' => 'Modifier les utilisateurs',
            'users.delete' => 'Supprimer les utilisateurs',
            'users.ban' => 'Bannir des utilisateurs',
            'content.moderate' => 'Modérer le contenu',
            'content.delete' => 'Supprimer du contenu',
            'messages.view' => 'Voir les messages',
            'messages.delete' => 'Supprimer les messages',
            'photos.view' => 'Voir les photos',
            'photos.delete' => 'Supprimer les photos',
            'families.manage' => 'Gérer les familles',
            'system.settings' => 'Paramètres système',
            'system.backup' => 'Sauvegarde système',
            'admins.manage' => 'Gérer les administrateurs',
            'logs.view' => 'Voir les logs',
            'analytics.view' => 'Voir les analytics',
        ];
    }

    /**
     * Rôles disponibles
     */
    public static function getAvailableRoles(): array
    {
        return [
            'moderator' => 'Modérateur',
            'admin' => 'Administrateur',
            'super_admin' => 'Super Administrateur',
        ];
    }
}
