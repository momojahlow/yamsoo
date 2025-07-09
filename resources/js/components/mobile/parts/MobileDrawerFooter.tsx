
import { DrawerFooter } from "@/components/ui/drawer";
import { Button } from "@/components/ui/button";
import { LogOut } from "lucide-react";

interface MobileDrawerFooterProps {
  onLogout: () => void;
}

export function MobileDrawerFooter({ onLogout }: MobileDrawerFooterProps) {
  return (
    <DrawerFooter className="pt-2">
      <Button 
        variant="outline" 
        className="w-full justify-start text-sm text-red-500 hover:bg-red-50 hover:text-red-600" 
        onClick={onLogout}
      >
        <LogOut className="h-4 w-4 mr-2" />
        Déconnexion
      </Button>
      <p className="text-xs text-center text-muted-foreground pt-2">Yamsoo v1.0.0</p>
    </DrawerFooter>
  );
}
