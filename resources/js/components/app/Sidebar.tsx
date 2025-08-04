
import { SidebarTrigger, SidebarContent, useSidebar, Sidebar } from "@/components/ui/sidebar";
import { useToast } from "@/hooks/use-toast";
import { useProfile } from "@/hooks/useProfileSimple";
import { useIsMobile } from "@/hooks/use-mobile";
import { useState } from "react";
import { Link } from "@inertiajs/react";
import { X } from "lucide-react";

import { SidebarMenuItems } from "./sidebar/SidebarMenuItems";
import { Profile as NotificationProfile } from "@/types/notifications";
import { useSuggestionCount } from "@/hooks/useSuggestionCount";
import { logout } from "@/utils/auth";

export function AppSidebar() {
  const { toast } = useToast();
  const { profile } = useProfile();
  const isMobile = useIsMobile();
  const { state } = useSidebar();

  // Convert profile to NotificationProfile type
  const notificationProfile = profile ? {
    id: profile.id ?? "", // Use nullish coalescing to handle undefined
    first_name: profile.first_name,
    last_name: profile.last_name,
    avatar_url: profile.avatar_url,
    gender: profile.gender,
    email: profile.email
  } as NotificationProfile : null;

  const { suggestionCount } = useSuggestionCount(notificationProfile);
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  // La sidebar doit être visible sur mobile aussi, mais avec un comportement différent
  // Elle sera en overlay sur mobile grâce à SidebarProvider

  const handleLogout = async () => {
    // Empêcher les clics multiples
    if (isLoggingOut) {
      console.log("Déconnexion déjà en cours, ignoré...");
      return;
    }

    try {
      setIsLoggingOut(true);
      console.log("Tentative de déconnexion...");

      const success = await logout();

      if (success) {
        toast({
          title: "Déconnexion réussie",
          description: "Vous avez été déconnecté avec succès",
          variant: "default",
        });
      } else {
        throw new Error('Erreur lors de la déconnexion');
      }
    } catch (error) {
      console.error('Erreur détaillée lors de la déconnexion:', error);
      toast({
        title: "Erreur",
        description: "Une erreur est survenue lors de la déconnexion",
        variant: "destructive",
      });
    } finally {
      setIsLoggingOut(false);
    }
  };

  return (
    <Sidebar
      collapsible={isMobile ? "offcanvas" : "icon"}
      variant="sidebar"
      className="border-r border-gray-200"
    >
      <SidebarContent className={`transition-all duration-300 ${
        isMobile
          ? 'w-72 min-w-72 max-w-72'
          : state === 'collapsed'
            ? 'w-16 min-w-16 max-w-16'
            : 'w-64 min-w-64 max-w-64'
      }`}>
        <div className="flex h-full flex-col bg-white">
          {/* Header avec logo Yamsoo et trigger */}
          <div className="p-2 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-orange-50 to-red-50">
            {/* Logo Yamsoo responsive */}
            <Link href="/" className="flex items-center space-x-2 group">
              {/* Logo thumb pour mobile et collapsed, complet pour expanded */}
              <div className="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-200">
                <span className="text-white font-bold text-sm">Y</span>
              </div>
              {/* Texte Yamsoo visible seulement quand expanded sur desktop ou toujours sur mobile */}
              {(isMobile || state !== 'collapsed') && (
                <span className="text-lg font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
                  Yamsoo!
                </span>
              )}
            </Link>

            {/* Trigger pour desktop, bouton fermer pour mobile */}
            {!isMobile ? (
              <SidebarTrigger className="ml-2" />
            ) : (
              <button
                onClick={() => setOpen(false)}
                className="ml-2 p-1.5 rounded-lg text-gray-600 hover:text-orange-600 hover:bg-orange-50 transition-colors duration-200"
              >
                <X className="w-5 h-5" />
              </button>
            )}
          </div>

          <div className="flex-1 flex flex-col">
            <SidebarMenuItems
              profile={notificationProfile}
              suggestionCount={suggestionCount}
              isCollapsed={!isMobile && state === 'collapsed'}
              handleLogout={handleLogout}
              isLoggingOut={isLoggingOut}
              isMobile={isMobile}
            />
          </div>
        </div>
      </SidebarContent>
    </Sidebar>
  );
}
