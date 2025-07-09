
import { format } from "date-fns";
import { fr } from "date-fns/locale";

interface MessageMetadataProps {
  createdAt: string;
  readAt?: string | null;
  isOwnMessage: boolean;
}

export function MessageMetadata({ createdAt, readAt, isOwnMessage }: MessageMetadataProps) {
  return (
    <div className="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
      <span>
        {format(new Date(createdAt), "d MMM HH:mm", {
          locale: fr,
        })}
      </span>
      {readAt && isOwnMessage && (
        <span className="text-primary">Lu</span>
      )}
    </div>
  );
}
