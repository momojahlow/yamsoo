<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'type', // 'text', 'image', 'file', 'audio', 'video'
        'file_url',
        'file_name',
        'file_size',
        'is_edited',
        'edited_at',
        'reply_to_id'
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'file_size' => 'integer'
    ];

    /**
     * Conversation du message
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Auteur du message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Message auquel celui-ci répond
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Réponses à ce message
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    /**
     * Réactions au message
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    /**
     * Obtenir l'URL du fichier
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Vérifier si le message est un fichier
     */
    public function getIsFileAttribute(): bool
    {
        return in_array($this->type, ['image', 'file', 'audio', 'video']);
    }

    /**
     * Obtenir la taille du fichier formatée
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Marquer le message comme édité
     */
    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now()
        ]);
    }

    /**
     * Obtenir le contenu formaté pour l'affichage
     */
    public function getFormattedContentAttribute(): string
    {
        if ($this->type === 'text') {
            // Convertir les URLs en liens
            $content = preg_replace(
                '/(https?:\/\/[^\s]+)/',
                '<a href="$1" target="_blank" class="text-blue-500 hover:underline">$1</a>',
                $this->content
            );

            // Convertir les mentions @username
            $content = preg_replace(
                '/@(\w+)/',
                '<span class="text-orange-500 font-medium">@$1</span>',
                $content
            );

            return $content;
        }

        return $this->content;
    }

    /**
     * Scope pour les messages récents
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope pour les messages d'une conversation
     */
    public function scopeInConversation($query, int $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    // Compatibilité avec l'ancien modèle
    public function sender(): BelongsTo
    {
        return $this->user();
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
