
import { useState, useRef } from 'react';
import { Button } from "@/components/ui/button";
import { Mic, Square, Loader2 } from "lucide-react";
import { useToast } from "@/hooks/use-toast";

interface VoiceRecorderProps {
  onRecordingComplete: (audioBlob: Blob) => Promise<void>;
  disabled?: boolean;
}

export function VoiceRecorder({ onRecordingComplete, disabled }: VoiceRecorderProps) {
  const [isRecording, setIsRecording] = useState(false);
  const [isProcessing, setIsProcessing] = useState(false);
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const chunksRef = useRef<Blob[]>([]);
  const streamRef = useRef<MediaStream | null>(null);
  const { toast } = useToast();

  const startRecording = async () => {
    if (disabled) return;
    
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      streamRef.current = stream;
      const mediaRecorder = new MediaRecorder(stream);
      mediaRecorderRef.current = mediaRecorder;
      chunksRef.current = [];

      mediaRecorder.ondataavailable = (e) => {
        if (e.data.size > 0) {
          chunksRef.current.push(e.data);
        }
      };

      mediaRecorder.onstop = async () => {
        if (chunksRef.current.length === 0) {
          toast({
            title: "Erreur",
            description: "Aucun audio enregistré",
            variant: "destructive",
          });
          return;
        }

        const audioBlob = new Blob(chunksRef.current, { type: 'audio/webm' });
        setIsProcessing(true);
        try {
          await onRecordingComplete(audioBlob);
        } catch (error) {
          console.error("Voice recorder error:", error);
          toast({
            title: "Erreur",
            description: "Impossible d'envoyer le message vocal",
            variant: "destructive",
          });
        } finally {
          setIsProcessing(false);
          // Clean up the stream
          if (streamRef.current) {
            streamRef.current.getTracks().forEach(track => track.stop());
            streamRef.current = null;
          }
        }
      };

      mediaRecorder.start();
      setIsRecording(true);
    } catch (error) {
      console.error("Error accessing microphone:", error);
      toast({
        title: "Erreur",
        description: "Impossible d'accéder au microphone",
        variant: "destructive",
      });
    }
  };

  const stopRecording = () => {
    if (mediaRecorderRef.current && isRecording) {
      mediaRecorderRef.current.stop();
      setIsRecording(false);
      
      // Clean up the stream to release the microphone
      if (streamRef.current) {
        streamRef.current.getTracks().forEach(track => track.stop());
      }
    }
  };

  return (
    <div className="flex items-center gap-2">
      {isProcessing ? (
        <Button size="icon" variant="outline" disabled className={`${isProcessing ? 'bg-amber-100' : ''}`}>
          <Loader2 className="h-4 w-4 animate-spin" />
        </Button>
      ) : isRecording ? (
        <Button
          size="icon"
          variant="destructive"
          onClick={stopRecording}
          disabled={disabled}
        >
          <Square className="h-4 w-4" />
        </Button>
      ) : (
        <Button
          size="icon"
          variant="outline"
          onClick={startRecording}
          disabled={disabled}
        >
          <Mic className="h-4 w-4" />
        </Button>
      )}
    </div>
  );
}
