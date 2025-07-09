
import { useState, useRef, ChangeEvent } from "react";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Paperclip, Send, Loader2 } from "lucide-react";
import data from '@emoji-mart/data';
import Picker from '@emoji-mart/react';
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { VoiceRecorder } from "./VoiceRecorder";
import { useIsMobile } from "@/hooks/use-mobile";
import { useToast } from "@/hooks/use-toast";

interface ChatInputProps {
  onSendMessage: (content: string, file?: File, audioBlob?: Blob) => void;
  onTyping: (isTyping: boolean) => void;
  disabled?: boolean;
}

export function ChatInput({ onSendMessage, onTyping, disabled }: ChatInputProps) {
  const [content, setContent] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const typingTimeoutRef = useRef<NodeJS.Timeout>();
  const isMobile = useIsMobile();
  const { toast } = useToast();

  const handleContentChange = (e: ChangeEvent<HTMLTextAreaElement>) => {
    setContent(e.target.value);
    
    if (typingTimeoutRef.current) {
      clearTimeout(typingTimeoutRef.current);
    }
    
    onTyping(true);
    typingTimeoutRef.current = setTimeout(() => {
      onTyping(false);
    }, 1000);
  };

  const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0];
    if (selectedFile) {
      setFile(selectedFile);
    }
  };

  const handleSend = async () => {
    if (!content.trim() && !file) return;

    setIsUploading(true);
    try {
      await onSendMessage(content, file || undefined);
      setContent("");
      setFile(null);
      if (fileInputRef.current) {
        fileInputRef.current.value = "";
      }
    } catch (error) {
      console.error("Error sending message:", error);
      toast({
        title: "Erreur",
        description: "Impossible d'envoyer le message",
        variant: "destructive",
      });
    } finally {
      setIsUploading(false);
    }
  };

  const handleVoiceMessage = async (audioBlob: Blob) => {
    setIsUploading(true);
    try {
      await onSendMessage("", undefined, audioBlob);
    } catch (error) {
      console.error("Error sending voice message:", error);
      toast({
        title: "Erreur",
        description: "Impossible d'envoyer le message vocal",
        variant: "destructive",
      });
    } finally {
      setIsUploading(false);
    }
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  const addEmoji = (emoji: any) => {
    setContent(prev => prev + emoji.native);
  };

  return (
    <div className="space-y-2">
      <Textarea
        placeholder="Écrivez votre message..."
        value={content}
        onChange={handleContentChange}
        onKeyPress={handleKeyPress}
        disabled={disabled || isUploading}
        className={`min-h-[60px] resize-none ${isMobile ? 'text-base' : 'min-h-[80px]'}`}
      />
      
      {file && (
        <div className="flex items-center gap-2 text-sm text-muted-foreground">
          <Paperclip className="h-4 w-4 flex-shrink-0" />
          <span className="truncate">{file.name}</span>
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setFile(null)}
          >
            Supprimer
          </Button>
        </div>
      )}

      <div className="flex justify-between items-center">
        <div className="flex gap-1">
          <input
            type="file"
            ref={fileInputRef}
            onChange={handleFileChange}
            accept="image/*,.pdf,.doc,.docx"
            className="hidden"
          />
          
          <Button
            variant="outline"
            size="icon"
            onClick={() => fileInputRef.current?.click()}
            disabled={disabled || isUploading}
            className={isMobile ? "h-9 w-9" : ""}
          >
            <Paperclip className="h-4 w-4" />
          </Button>

          <Popover>
            <PopoverTrigger asChild>
              <Button 
                variant="outline" 
                size="icon" 
                disabled={disabled || isUploading}
                className={isMobile ? "h-9 w-9" : ""}
              >
                😊
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="start" side="top">
              <Picker
                data={data}
                onEmojiSelect={addEmoji}
                theme="light"
                set="native"
                {...(isMobile ? { perLine: 6 } : {})}
              />
            </PopoverContent>
          </Popover>

          <VoiceRecorder onRecordingComplete={handleVoiceMessage} disabled={disabled || isUploading} />
        </div>

        <Button 
          onClick={handleSend}
          disabled={(!content.trim() && !file) || disabled || isUploading}
          size={isMobile ? "sm" : "default"}
        >
          {isUploading ? (
            <Loader2 className="h-4 w-4 animate-spin" />
          ) : (
            <>
              <Send className="h-4 w-4 mr-2" />
              {!isMobile && "Envoyer"}
            </>
          )}
        </Button>
      </div>
    </div>
  );
}
