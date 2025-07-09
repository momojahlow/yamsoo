
import { Message } from "@/types/chat";
import { AudioMessage } from "./AudioMessage";
import { FileAttachment } from "./FileAttachment";
import { ImageAttachment } from "./ImageAttachment";
import { isImageUrl } from "@/utils/imageUtils";

interface MessageContentProps {
  message: Message;
  isOwnMessage: boolean;
}

export function MessageContent({ message, isOwnMessage }: MessageContentProps) {
  const messageId = `msg-${message.id}`;
  const isImageAttachment = message.attachment_url ? isImageUrl(message.attachment_url) : false;

  return (
    <div
      className={`rounded-lg p-3 ${
        isOwnMessage
          ? "bg-primary text-primary-foreground"
          : "bg-muted"
      }`}
    >
      {message.content && <p>{message.content}</p>}

      {message.audio_url && (
        <AudioMessage 
          audioUrl={message.audio_url} 
          audioDuration={message.audio_duration} 
        />
      )}

      {message.attachment_url && isImageAttachment && (
        <ImageAttachment 
          url={message.attachment_url} 
          messageId={messageId}
        />
      )}

      {message.attachment_url && !isImageAttachment && (
        <FileAttachment 
          url={message.attachment_url} 
          name={message.attachment_name} 
        />
      )}
    </div>
  );
}
