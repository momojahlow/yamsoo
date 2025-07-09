
import {
  Dialog,
  DialogContent,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Share2 } from "lucide-react";
import { ShareContactsList } from "./ShareContactsList";
import { useState } from "react";

interface ShareImageDialogProps {
  imageUrl: string;
  trigger?: React.ReactNode;
}

export function ShareImageDialog({ imageUrl, trigger }: ShareImageDialogProps) {
  const [open, setOpen] = useState(false);

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger || (
          <Button
            variant="secondary"
            size="icon"
            className="bg-background/80 backdrop-blur-sm"
          >
            <Share2 className="h-4 w-4" />
          </Button>
        )}
      </DialogTrigger>
      <DialogContent className="sm:max-w-[425px]">
        <ShareContactsList 
          imageUrl={imageUrl} 
          onClose={() => setOpen(false)} 
        />
      </DialogContent>
    </Dialog>
  );
}
