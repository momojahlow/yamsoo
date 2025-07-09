
import { useState } from "react";
import { ShareImageDialog } from "@/components/share/ShareImageDialog";

interface ImageAttachmentProps {
  url: string;
  messageId: string;
}

export function ImageAttachment({ url, messageId }: ImageAttachmentProps) {
  const [isHovered, setIsHovered] = useState(false);

  return (
    <div 
      className="mt-2 relative"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      <img 
        src={url} 
        alt="Image jointe" 
        className="max-w-full rounded-md max-h-[300px] object-contain"
        loading="lazy"
      />
      {isHovered && (
        <div className="absolute top-2 right-2">
          <ShareImageDialog imageUrl={url} />
        </div>
      )}
    </div>
  );
}
