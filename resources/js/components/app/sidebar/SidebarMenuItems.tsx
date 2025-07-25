
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
  Camera,
  Shield
} from "lucide-react";
import { cn } from "@/lib/utils";
import { NotificationsBadge } from "./NotificationsBadge";
import { Profile } from "@/types/notifications";
import { useUnreadMessages } from "@/hooks/useUnreadMessages";
import { router } from "@inertiajs/react";

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
      {/* Yamsoo menu item - Commenté car la fonctionnalité est accessible via le menu Réseaux */}
      {/*
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
      */}

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Accueil"
          onClick={() => router.visit("/dashboard")}
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
          onClick={() => router.visit("/profil")}
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
          onClick={() => router.visit("/famille")}
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
          onClick={() => router.visit("/photo-albums")}
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
          onClick={() => router.visit("/reseaux")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/reseaux" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Network className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Réseaux</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem className="relative">
        <SidebarMenuButton
          tooltip="Messages"
          onClick={() => router.visit("/messages")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/messages" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <MessageSquare className="h-6 w-6" />
          {!isCollapsed && <span className="ml-2">Messages</span>}
        </SidebarMenuButton>
        {/* Badge pour messages non lus - Version ultra-simple */}
        {unreadCount > 0 && (
          <span
            className={cn(
              "absolute bg-red-500 text-white rounded-full font-bold shadow-lg border-2 border-white flex items-center justify-center",
              isCollapsed
                ? "top-0 right-0 w-4 h-4 text-[10px] -translate-y-1 translate-x-1"
                : "top-0 right-0 min-w-[18px] h-[18px] text-[10px] -translate-y-2 translate-x-2"
            )}
            style={{
              zIndex: 9999,
              pointerEvents: 'none',
              fontSize: '10px',
              lineHeight: '1'
            }}
          >
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        )}
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip="Notifications"
          onClick={() => router.visit("/notifications")}
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
          onClick={() => router.visit("/suggestions")}
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

      {/* Lien Administration pour les admins */}
      {profile?.user?.role && ['admin', 'super_admin'].includes(profile.user.role) && (
        <SidebarMenuItem className="mt-3">
          <SidebarMenuButton
            tooltip="Administration"
            onClick={() => router.visit("/admin")}
            className="w-full justify-start transition-all duration-200 hover:scale-105 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 font-medium border border-purple-200 dark:border-purple-800 hover:border-purple-300 dark:hover:border-purple-700 rounded-lg"
          >
            <Shield className="h-6 w-6" />
            {!isCollapsed && <span className="ml-2">Administration</span>}
          </SidebarMenuButton>
        </SidebarMenuItem>
      )}

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
