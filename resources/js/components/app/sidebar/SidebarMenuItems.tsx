
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
  LogOut,
  User,
  Camera
} from "lucide-react";
import { cn } from "@/lib/utils";
import { NotificationsBadge } from "./NotificationsBadge";
import { Profile } from "@/types/notifications";
import { useUnreadMessages } from "@/hooks/useUnreadMessages";

interface SidebarMenuItemsProps {
  profile: Profile | null;
  suggestionCount: number;
  isCollapsed?: boolean;
  handleLogout: () => Promise<void>;
}

export function SidebarMenuItems({ profile, suggestionCount, isCollapsed = false, handleLogout }: SidebarMenuItemsProps) {
  const { unreadCount } = useUnreadMessages();

  return (
    <SidebarMenu className="flex flex-col h-full space-y-3 px-1">
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
          tooltip="Mon Profil"
          onClick={() => window.location.href = "/profil"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/profil" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <User className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Mon Profil</span>}
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
          tooltip="Albums Photo"
          onClick={() => window.location.href = "/photo-albums"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname.startsWith("/photo-albums") ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Camera className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Albums Photo</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Réseaux"
          onClick={() => window.location.href = "/reseaux"}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/reseaux" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Network className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Réseaux</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <div className="relative">
          <SidebarMenuButton
            tooltip="Messages"
            onClick={() => window.location.href = "/messages"}
            className={cn(
              "w-full justify-start transition-transform duration-200 hover:scale-110",
              window.location.pathname === "/messages" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
            )}
          >
            <MessageSquare className="h-6 w-6" />
            {!isCollapsed && <span className="ml-2">Messages</span>}
          </SidebarMenuButton>
          {/* Badge pour messages non lus */}
          {unreadCount > 0 && (
            <span className={cn(
              "absolute bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-medium shadow-lg border-2 border-white z-10",
              isCollapsed
                ? "-top-1 -right-1 min-w-[18px] h-[18px] text-[10px]"
                : "-top-2 -right-2 min-w-[20px] h-5 px-1"
            )}>
              {unreadCount > 99 ? '99+' : unreadCount}
            </span>
          )}
        </div>
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

      {/* Bouton de déconnexion juste après Suggestions */}
      <SidebarMenuItem className="mt-3">
        <SidebarMenuButton
          tooltip="Déconnexion"
          onClick={handleLogout}
          className="w-full justify-start transition-all duration-200 hover:scale-105 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium border border-red-200 dark:border-red-800 hover:border-red-300 dark:hover:border-red-700 rounded-lg"
        >
          <LogOut className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Déconnexion</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      {/* Spacer pour pousser le reste vers le bas si nécessaire */}
      <div className="mt-auto"></div>
    </SidebarMenu>
  );
}
