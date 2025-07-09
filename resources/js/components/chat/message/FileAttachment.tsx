
import { Button } from "@/components/ui/button";
import { Paperclip } from "lucide-react";

interface FileAttachmentProps {
  url: string;
  name?: string | null;
}

export function FileAttachment({ url, name }: FileAttachmentProps) {
  return (
    <Button
      variant="outline"
      size="sm"
      onClick={() => window.open(url, "_blank")}
      className="mt-2 text-sm"
    >
      <Paperclip className="h-4 w-4 mr-2" />
      {name || "Pièce jointe"}
    </Button>
  );
}
