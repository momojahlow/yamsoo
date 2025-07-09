
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { 
  Drawer,
  DrawerContent
} from "@/components/ui/drawer";
import { useProfile } from "@/hooks/useProfile";
import { useToast } from "@/hooks/use-toast";
import { supabase } from "@/integrations/supabase/client";
import { Profile as NotificationProfile } from "@/types/notifications";
import { useSuggestionCount } from "@/hooks/useSuggestionCount";

// Import the refactored components
import { MobileDrawerHeader } from "./parts/MobileDrawerHeader";
import { MobileDrawerMenuItems } from "./parts/MobileDrawerMenuItems";
import { MobileDrawerThemeToggle } from "./parts/MobileDrawerThemeToggle";
import { MobileDrawerFooter } from "./parts/MobileDrawerFooter";

interface MobileDrawerMenuProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export function MobileDrawerMenu({ open, onOpenChange }: MobileDrawerMenuProps) {
  const navigate = useNavigate();
  const { toast } = useToast();
  const { profile } = useProfile();
  
  // Convert profile to NotificationProfile type
  const typedProfile = profile ? {
    id: profile.id ?? "", // Use nullish coalescing to handle undefined
    first_name: profile.first_name,
    last_name: profile.last_name,
    avatar_url: profile.avatar_url,
    gender: profile.gender,
    email: profile.email
  } as NotificationProfile : null;
  
  const { suggestionCount } = useSuggestionCount(typedProfile);

  const handleLogout = async () => {
    try {
      console.log("Tentative de déconnexion depuis le drawer mobile...");
      const { error } = await supabase.auth.signOut();
      
      if (error) {
        throw error;
      }
      
      toast({
        title: "Déconnexion réussie",
        description: "Vous avez été déconnecté avec succès",
        variant: "default",
      });
      
      // Close drawer when logging out
      onOpenChange(false);
      navigate("/auth", { replace: true });
    } catch (error) {
      console.error("Erreur lors de la déconnexion:", error);
      toast({
        title: "Erreur",
        description: "Une erreur est survenue lors de la déconnexion",
        variant: "destructive",
      });
    }
  };

  const handleNavigation = (path: string) => {
    navigate(path);
    onOpenChange(false);
  };

  return (
    <Drawer open={open} onOpenChange={onOpenChange} direction="left">
      <DrawerContent className="h-[85vh] rounded-t-[14px] safe-area">
        <MobileDrawerHeader profile={typedProfile} />
        <MobileDrawerMenuItems 
          onNavigation={handleNavigation} 
          suggestionCount={suggestionCount} 
        />
        <MobileDrawerThemeToggle />
        <MobileDrawerFooter onLogout={handleLogout} />
      </DrawerContent>
    </Drawer>
  );
}
