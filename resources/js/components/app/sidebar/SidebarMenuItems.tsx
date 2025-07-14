
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

  return (
    <SidebarMenu className="flex-grow space-y-3 px-1">
      {/* Yamsoo menu item */}
      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Yamsoo"
          onClick={() => window.location.href = "/reseaux"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110 bg-gradient-to-r from-fuchsia-500 to-amber-400 text-white font-bold",
            window.location.pathname === "/reseaux" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Sparkles className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Yamsoo</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Accueil"
          onClick={() => window.location.href = "/dashboard"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/dashboard" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Calendar className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Accueil</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Famille"
          onClick={() => window.location.href = "/famille"}
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
          onClick={() => window.location.href = "/networks"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/networks" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Network className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Réseaux</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Messages"
          onClick={() => window.location.href = "/messagerie"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/messagerie" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <MessageSquare className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Messages</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Notifications"
          onClick={() => window.location.href = "/notifications"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110 relative",
            window.location.pathname === "/notifications" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Bell className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Notifications</span>}
          <NotificationsBadge profile={profile} isCollapsed={isCollapsed} hideWhenCollapsed={true} />
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Suggestions"
          onClick={() => window.location.href = "/suggestions"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110 relative",
            "bg-amber-50 text-amber-600 mt-2",
            window.location.pathname === "/suggestions" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
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
