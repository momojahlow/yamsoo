
import { useNavigate } from "react-router-dom";
import {
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton
} from "@/components/ui/sidebar";
import {
  Calendar,
  Trees,
  Network,
  MessageSquare,
  Bell,
  Sparkles,
  LogOut
} from "lucide-react";
import { cn } from "@/lib/utils";
import { NotificationsBadge } from "./NotificationsBadge";
import { Profile } from "@/types/notifications";

interface SidebarMenuItemsProps {
  profile: Profile | null;
  suggestionCount: number;
  isCollapsed?: boolean;
  handleLogout: () => Promise<void>;
}

export function SidebarMenuItems({ profile, suggestionCount, isCollapsed = false, handleLogout }: SidebarMenuItemsProps) {
  const navigate = useNavigate();
  
  return (
    <SidebarMenu className="flex-grow space-y-3 px-1">
      <SidebarMenuItem>
        <SidebarMenuButton 
          tooltip="Accueil"
          onClick={() => navigate("/dashboard")} 
          className="w-full justify-start transition-transform duration-200 hover:scale-110"
        >
          <Calendar className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Accueil</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>
      
      <SidebarMenuItem>
        <SidebarMenuButton 
          tooltip="Famille"
          onClick={() => navigate("/famille")} 
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/famille" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Trees className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Famille</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>
      
      <SidebarMenuItem>
        <SidebarMenuButton 
          tooltip="Réseaux"
          onClick={() => navigate("/networks")} 
          className="w-full justify-start transition-transform duration-200 hover:scale-110"
        >
          <Network className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Réseaux</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>
      
      <SidebarMenuItem>
        <SidebarMenuButton 
          tooltip="Messages"
          onClick={() => navigate("/messagerie")} 
          className="w-full justify-start transition-transform duration-200 hover:scale-110"
        >
          <MessageSquare className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Messages</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>
      
      <SidebarMenuItem>
        <SidebarMenuButton 
          tooltip="Notifications"
          onClick={() => navigate("/notifications")} 
          className="w-full justify-start transition-transform duration-200 hover:scale-110 relative"
        >
          <Bell className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Notifications</span>}
          <NotificationsBadge profile={profile} isCollapsed={isCollapsed} hideWhenCollapsed={true} />
        </SidebarMenuButton>
      </SidebarMenuItem>
      
      <SidebarMenuItem>
        <SidebarMenuButton 
          tooltip="Suggestions"
          onClick={() => navigate("/suggestions")} 
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110 relative",
            "bg-amber-50 text-amber-600 mt-2"
          )}
        >
          <Sparkles className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Suggestions</span>}
          {suggestionCount > 0 && !isCollapsed && (
            <span className="absolute min-w-5 h-5 flex items-center justify-center rounded-full bg-red-500 text-[11px] font-medium text-white top-1 right-2">
              {suggestionCount}
            </span>
          )}
        </SidebarMenuButton>
      </SidebarMenuItem>
      
      <SidebarMenuItem className="mt-auto">
        <SidebarMenuButton 
          tooltip="Déconnexion"
          onClick={handleLogout} 
          className="w-full justify-start transition-transform duration-200 hover:scale-110 text-red-500 hover:bg-red-50"
        >
          <LogOut className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Déconnexion</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  );
}
