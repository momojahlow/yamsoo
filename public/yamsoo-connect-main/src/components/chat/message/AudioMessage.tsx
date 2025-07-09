
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { PlayCircle, Pause, Loader2 } from "lucide-react";
import { formatTime } from "@/utils/formatUtils";

interface AudioMessageProps {
  audioUrl: string;
  audioDuration?: number | null;
}

export function AudioMessage({ audioUrl, audioDuration }: AudioMessageProps) {
  const [isPlaying, setIsPlaying] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const formattedDuration = audioDuration ? formatTime(audioDuration) : "0:00";
  
  const handlePlayVoice = () => {
    if (isPlaying) {
      window.currentAudio?.pause();
      setIsPlaying(false);
      return;
    }
    
    setIsLoading(true);
    
    const audio = new Audio(audioUrl);
    // Store reference to current audio for global control
    window.currentAudio = audio;
    
    audio.oncanplaythrough = () => {
      setIsLoading(false);
      audio.play();
      setIsPlaying(true);
    };
    
    audio.onended = () => {
      setIsPlaying(false);
    };
    
    audio.onerror = () => {
      setIsPlaying(false);
      setIsLoading(false);
    };
  };

  return (
    <div className="mt-2 flex items-center gap-2">
      <Button
        variant="outline"
        size="icon"
        onClick={handlePlayVoice}
        className="h-10 w-10 rounded-full bg-orange-400 hover:bg-orange-500 border-none"
        disabled={isLoading}
      >
        {isLoading ? (
          <Loader2 className="h-5 w-5 text-white animate-spin" />
        ) : isPlaying ? (
          <Pause className="h-5 w-5 text-white" />
        ) : (
          <PlayCircle className="h-5 w-5 text-white" />
        )}
      </Button>
      <div className="bg-orange-400 text-white p-3 rounded-xl px-4 min-w-[120px] flex items-center">
        <span>Message vocal</span>
        <span className="ml-2 text-sm opacity-80">({formattedDuration})</span>
      </div>
    </div>
  );
}
