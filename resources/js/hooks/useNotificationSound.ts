import { useRef, useCallback, useEffect } from 'react';

interface User {
    id: number;
    name: string;
}

interface Message {
    id: number;
    content: string;
    user: User;
    conversation_id: number;
    created_at: string;
}

interface NotificationSoundOptions {
    enabled?: boolean;
    volume?: number;
    soundUrl?: string;
}

export const useNotificationSound = (options: NotificationSoundOptions = {}) => {
    const audioRef = useRef<HTMLAudioElement | null>(null);
    const lastPlayedRef = useRef<number>(0);
    const playingRef = useRef<boolean>(false);
    const recentMessagesRef = useRef<Set<number>>(new Set());

    const {
        enabled = true,
        volume = 0.7,
        soundUrl = '/notifications/alert-sound.mp3'
    } = options;

    // Initialiser l'audio avec le fichier spécifié
    useEffect(() => {
        if (enabled) {
            const audio = new Audio(soundUrl);
            audio.volume = volume;
            audio.preload = 'auto';

            // Gérer les événements audio
            const handleCanPlay = () => {
                console.log('🔊 Audio notification prêt:', soundUrl);
            };

            const handleError = (e: Event) => {
                console.error('❌ Erreur lors du chargement du son de notification:', e);
                // Fallback vers Web Audio API
                createWebAudioFallback();
            };

            const handleEnded = () => {
                playingRef.current = false;
            };

            audio.addEventListener('canplay', handleCanPlay);
            audio.addEventListener('error', handleError);
            audio.addEventListener('ended', handleEnded);

            audioRef.current = audio;

            return () => {
                audio.removeEventListener('canplay', handleCanPlay);
                audio.removeEventListener('error', handleError);
                audio.removeEventListener('ended', handleEnded);
                audio.pause();
                audio.src = '';
            };
        }
    }, [enabled, soundUrl, volume]);

    // Créer un fallback avec Web Audio API
    const createWebAudioFallback = useCallback(() => {
        try {
            const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();

            const createNotificationSound = () => {
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                // Configuration du son (notification douce)
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);

                gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + 0.01);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            };

            audioRef.current = {
                play: createNotificationSound,
                volume: volume
            } as any;
        } catch (error) {
            console.error('❌ Erreur lors de la création du fallback Web Audio:', error);
        }
    }, [volume]);

    // Nettoyer les anciens messages de la liste des récents
    useEffect(() => {
        const interval = setInterval(() => {
            const now = Date.now();
            const fiveSecondsAgo = now - 5000;

            recentMessagesRef.current.forEach(messageId => {
                if (messageId < fiveSecondsAgo) {
                    recentMessagesRef.current.delete(messageId);
                }
            });
        }, 1000);

        return () => clearInterval(interval);
    }, []);

    const playNotificationSound = useCallback(async (message?: Message, currentUserId?: number, notificationsEnabled: boolean = true) => {
        // Vérifications de base
        if (!enabled || !notificationsEnabled || !audioRef.current) {
            console.log('🔇 Notifications sonores désactivées');
            return;
        }

        // Ne pas jouer le son pour ses propres messages
        if (message && currentUserId && message.user.id === currentUserId) {
            console.log('🔇 Pas de son pour ses propres messages');
            return;
        }

        // Éviter les doublons de messages récents
        if (message) {
            const messageKey = message.id;
            if (recentMessagesRef.current.has(messageKey)) {
                console.log('🔇 Message déjà traité récemment, pas de son');
                return;
            }
            recentMessagesRef.current.add(messageKey);
        }

        // Éviter de jouer plusieurs sons en rafale (throttling)
        const now = Date.now();
        const timeSinceLastPlay = now - lastPlayedRef.current;
        const minInterval = 1000; // 1 seconde minimum entre les sons

        if (timeSinceLastPlay < minInterval) {
            console.log('🔇 Son throttlé, trop récent');
            return;
        }

        // Éviter de jouer si un son est déjà en cours
        if (playingRef.current) {
            console.log('🔇 Son déjà en cours de lecture');
            return;
        }

        try {
            // Réinitialiser l'audio au début
            if (audioRef.current.currentTime !== undefined) {
                audioRef.current.currentTime = 0;
            }
            playingRef.current = true;
            lastPlayedRef.current = now;

            // Jouer le son
            const playPromise = audioRef.current.play();

            if (playPromise !== undefined) {
                await playPromise;
                console.log('🔊 Son de notification joué', message ? `pour message de ${message.user.name}` : '');
            }
        } catch (error) {
            console.error('❌ Erreur lors de la lecture du son:', error);
            playingRef.current = false;

            // Si l'erreur est due à l'interaction utilisateur requise
            if (error instanceof DOMException && error.name === 'NotAllowedError') {
                console.warn('⚠️ Lecture audio bloquée - interaction utilisateur requise');
            }
        }
    }, [enabled]);

    const playMessageSent = useCallback(() => {
        try {
            // Son plus discret pour les messages envoyés
            const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.setValueAtTime(600, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0, audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (error) {
            console.log('Erreur lors de la lecture du son d\'envoi:', error);
        }
    }, []);

    const testSound = useCallback(async () => {
        console.log('🧪 Test du son de notification');
        await playNotificationSound();
    }, [playNotificationSound]);

    const setVolume = useCallback((newVolume: number) => {
        if (audioRef.current && audioRef.current.volume !== undefined) {
            audioRef.current.volume = Math.max(0, Math.min(1, newVolume));
        }
    }, []);

    // Compatibilité avec l'ancienne API
    const playNotification = useCallback(() => {
        playNotificationSound();
    }, [playNotificationSound]);

    return {
        playNotification, // Ancienne API pour compatibilité
        playNotificationSound, // Nouvelle API avec paramètres
        playMessageSent,
        testSound,
        setVolume,
        isEnabled: enabled
    };
};
