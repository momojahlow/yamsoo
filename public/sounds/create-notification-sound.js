// Script pour créer un fichier audio de notification simple
// Exécuter dans la console du navigateur pour générer le son

function createNotificationSound() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const sampleRate = audioContext.sampleRate;
    const duration = 0.5; // 0.5 secondes
    const frameCount = sampleRate * duration;
    
    const audioBuffer = audioContext.createBuffer(1, frameCount, sampleRate);
    const channelData = audioBuffer.getChannelData(0);
    
    // Générer un son de notification (deux bips)
    for (let i = 0; i < frameCount; i++) {
        const time = i / sampleRate;
        let sample = 0;
        
        // Premier bip (800Hz)
        if (time < 0.15) {
            sample = Math.sin(2 * Math.PI * 800 * time) * Math.exp(-time * 5);
        }
        // Pause
        else if (time < 0.25) {
            sample = 0;
        }
        // Deuxième bip (600Hz)
        else if (time < 0.4) {
            sample = Math.sin(2 * Math.PI * 600 * time) * Math.exp(-(time - 0.25) * 5);
        }
        
        channelData[i] = sample * 0.3; // Volume à 30%
    }
    
    return audioBuffer;
}

// Fonction pour télécharger le son généré
function downloadNotificationSound() {
    const audioBuffer = createNotificationSound();
    
    // Convertir en WAV (simplifié)
    const length = audioBuffer.length;
    const arrayBuffer = new ArrayBuffer(44 + length * 2);
    const view = new DataView(arrayBuffer);
    
    // En-tête WAV
    const writeString = (offset, string) => {
        for (let i = 0; i < string.length; i++) {
            view.setUint8(offset + i, string.charCodeAt(i));
        }
    };
    
    writeString(0, 'RIFF');
    view.setUint32(4, 36 + length * 2, true);
    writeString(8, 'WAVE');
    writeString(12, 'fmt ');
    view.setUint32(16, 16, true);
    view.setUint16(20, 1, true);
    view.setUint16(22, 1, true);
    view.setUint32(24, audioBuffer.sampleRate, true);
    view.setUint32(28, audioBuffer.sampleRate * 2, true);
    view.setUint16(32, 2, true);
    view.setUint16(34, 16, true);
    writeString(36, 'data');
    view.setUint32(40, length * 2, true);
    
    // Données audio
    const channelData = audioBuffer.getChannelData(0);
    let offset = 44;
    for (let i = 0; i < length; i++) {
        const sample = Math.max(-1, Math.min(1, channelData[i]));
        view.setInt16(offset, sample * 0x7FFF, true);
        offset += 2;
    }
    
    // Télécharger
    const blob = new Blob([arrayBuffer], { type: 'audio/wav' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'notification.wav';
    a.click();
    URL.revokeObjectURL(url);
}

console.log('Pour créer le fichier audio, exécutez: downloadNotificationSound()');
