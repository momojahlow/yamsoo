import { useRef, useCallback } from 'react';

export const useNotificationSound = () => {
    const audioRef = useRef<HTMLAudioElement | null>(null);

    // Créer l'audio une seule fois
    const initAudio = useCallback(() => {
        if (!audioRef.current) {
            // Créer un son de notification simple avec Web Audio API
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

            // Alternative: utiliser un fichier audio si disponible
            const audio = new Audio();
            
            // Son de notification WhatsApp-like (data URL d'un son simple)
            const notificationSound = 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT';
            
            audio.src = notificationSound;
            audio.volume = 0.5;
            audio.preload = 'auto';
            
            audioRef.current = audio;
            
            // Fallback vers Web Audio API si le fichier audio ne fonctionne pas
            audio.onerror = () => {
                audioRef.current = {
                    play: createNotificationSound
                } as any;
            };
        }
    }, []);

    const playNotification = useCallback(() => {
        try {
            initAudio();
            
            if (audioRef.current) {
                // Vérifier si l'utilisateur a interagi avec la page (requis pour l'autoplay)
                const playPromise = audioRef.current.play();
                
                if (playPromise !== undefined) {
                    playPromise.catch((error) => {
                        console.log('Notification audio bloquée par le navigateur:', error);
                        // Fallback silencieux - pas d'erreur visible pour l'utilisateur
                    });
                }
            }
        } catch (error) {
            console.log('Erreur lors de la lecture du son de notification:', error);
        }
    }, [initAudio]);

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

    return {
        playNotification,
        playMessageSent
    };
};
