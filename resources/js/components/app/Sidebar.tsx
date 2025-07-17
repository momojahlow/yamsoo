
import { SidebarTrigger, SidebarContent, useSidebar } from "@/components/ui/sidebar";
import { useToast } from "@/hooks/use-toast";
import { useProfile } from "@/hooks/useProfileSimple";
import { useIsMobile } from "@/hooks/use-mobile";
import { SidebarAvatar } from "./sidebar/SidebarAvatar";
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

  // Si on est sur mobile, ne pas afficher la sidebar
  if (isMobile) return null;

    const handleLogout = async () => {
    try {
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
    }
  };

  return (
    <SidebarContent className={`transition-all duration-300 ${state === 'collapsed' ? 'w-16 min-w-16 max-w-16' : 'w-64 min-w-64 max-w-64'}`}>
      <div className="flex h-full flex-col">
        <div className="p-2 flex items-center">
          <SidebarTrigger className="ml-0.5" />
        </div>

        <SidebarAvatar profile={notificationProfile} />

        <div className="flex-1 flex flex-col">
          <SidebarMenuItems
            profile={notificationProfile}
            suggestionCount={suggestionCount}
            isCollapsed={state === 'collapsed'}
            handleLogout={handleLogout}
          />
        </div>
      </div>
    </SidebarContent>
  );
}
