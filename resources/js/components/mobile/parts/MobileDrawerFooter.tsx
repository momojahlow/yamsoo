
import { DrawerFooter } from "@/components/ui/drawer";
import { Button } from "@/components/ui/button";
import { LogOut } from "lucide-react";
import { cn } from "@/lib/utils";

interface MobileDrawerFooterProps {
  onLogout: () => void;
  isLoggingOut?: boolean;
}

export function MobileDrawerFooter({ onLogout, isLoggingOut = false }: MobileDrawerFooterProps) {
  return (
    <DrawerFooter className="pt-2">
      <Button
        variant="outline"
        className={cn(
          "w-full justify-start text-sm text-red-500 hover:bg-red-50 hover:text-red-600",
          isLoggingOut && "opacity-50 cursor-not-allowed"
        )}
        onClick={onLogout}
        disabled={isLoggingOut}
      >
        <LogOut className={cn("h-4 w-4 mr-2", isLoggingOut && "animate-spin")} />
        {isLoggingOut ? "Déconnexion..." : "Déconnexion"}
      </Button>
      <p className="text-xs text-center text-muted-foreground pt-2">Yamsoo v1.0.0</p>
    </DrawerFooter>
  );
}
