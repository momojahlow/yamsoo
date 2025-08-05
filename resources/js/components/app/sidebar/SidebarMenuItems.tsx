
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
import { useTranslation } from "@/hooks/useTranslation";

interface SidebarMenuItemsProps {
  profile: Profile | null;
  suggestionCount: number;
  isCollapsed?: boolean;
  handleLogout: () => Promise<void>;
  isLoggingOut?: boolean;
  isMobile?: boolean;
}

export function SidebarMenuItems({ profile, suggestionCount, isCollapsed = false, handleLogout, isLoggingOut = false, isMobile = false }: SidebarMenuItemsProps) {
  const { unreadCount } = useUnreadMessages();
  const { t, isRTL } = useTranslation();

  return (
    <SidebarMenu className={cn(
      "flex flex-col h-full space-y-2 px-1",
      isMobile ? "space-y-1 px-2" : "space-y-3 px-1"
    )}>
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
          tooltip={t('dashboard')}
          onClick={() => router.visit("/dashboard")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/dashboard" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Calendar className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('dashboard')}</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip={t('profile')}
          onClick={() => router.visit("/profil")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/profil" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <User className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('profile')}</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip={t('family')}
          onClick={() => router.visit("/famille")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/famille" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Trees className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('family')}</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip={t('photo_albums')}
          onClick={() => router.visit("/photo-albums")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname.startsWith("/photo-albums") ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Camera className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('photo_albums')}</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip={t('networks')}
          onClick={() => router.visit("/reseaux")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/reseaux" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Network className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('networks')}</span>}
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem className="relative">
        <SidebarMenuButton
          tooltip={t('messages')}
          onClick={() => router.visit("/messages")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110",
            window.location.pathname === "/messages" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <MessageSquare className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('messages')}</span>}
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
          tooltip={t('notifications')}
          onClick={() => router.visit("/notifications")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110 relative",
            window.location.pathname === "/notifications" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Bell className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('notifications')}</span>}
          <NotificationsBadge profile={profile} isCollapsed={isCollapsed && !isMobile} hideWhenCollapsed={true} />
        </SidebarMenuButton>
      </SidebarMenuItem>

      <SidebarMenuItem>
        <SidebarMenuButton
          tooltip={t('suggestions')}
          onClick={() => router.visit("/suggestions")}
          className={cn(
            "w-full justify-start transition-transform duration-200 hover:scale-110 relative",
            "bg-amber-50 text-amber-600 mt-2",
            window.location.pathname === "/suggestions" ? "bg-sidebar-accent text-sidebar-accent-foreground" : ""
          )}
        >
          <Sparkles className="h-5 w-5 sm:h-6 sm:w-6" />
          {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('suggestions')}</span>}
          {suggestionCount > 0 && (!isCollapsed || isMobile) && (
            <span className={`absolute min-w-5 h-5 flex items-center justify-center rounded-full bg-red-500 text-[11px] font-medium text-white top-1 ${isRTL ? 'left-2' : 'right-2'}`}>
            {suggestionCount}
            </span>
          )}
        </SidebarMenuButton>
      </SidebarMenuItem>

      {/* Lien Administration pour les admins */}
      {profile?.user?.role && ['admin', 'super_admin'].includes(profile.user.role) && (
        <SidebarMenuItem className="mt-3">
          <SidebarMenuButton
            tooltip={t('settings')}
            onClick={() => router.visit("/admin")}
            className="w-full justify-start transition-all duration-200 hover:scale-105 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 font-medium border border-purple-200 dark:border-purple-800 hover:border-purple-300 dark:hover:border-purple-700 rounded-lg"
          >
            <Shield className="h-5 w-5 sm:h-6 sm:w-6" />
            {(!isCollapsed || isMobile) && <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>{t('settings')}</span>}
          </SidebarMenuButton>
        </SidebarMenuItem>
      )}

      {/* Bouton de déconnexion juste après Suggestions */}
      <SidebarMenuItem className="mt-3">
        <SidebarMenuButton
          tooltip={isLoggingOut ? t('loading') : t('logout')}
          onClick={handleLogout}
          disabled={isLoggingOut}
          className={cn(
            "w-full justify-start transition-all duration-200 hover:scale-105 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium border border-red-200 dark:border-red-800 hover:border-red-300 dark:hover:border-red-700 rounded-lg",
            isLoggingOut && "opacity-50 cursor-not-allowed hover:scale-100"
          )}
        >
          <LogOut className={cn("h-5 w-5 sm:h-6 sm:w-6", isLoggingOut && "animate-spin")} />
          {(!isCollapsed || isMobile) && (
            <span className={`${isRTL ? 'mr-2' : 'ml-2'} text-sm sm:text-base`}>
              {isLoggingOut ? t('loading') : t('logout')}
            </span>
          )}
        </SidebarMenuButton>
      </SidebarMenuItem>

      {/* Spacer pour pousser le reste vers le bas si nécessaire */}
      <div className="mt-auto"></div>
    </SidebarMenu>
  );
}
