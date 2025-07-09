import { useState, useEffect } from "react";
import { Menu, MessageSquare, Trees, Bell, Home, Network, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";
import { MobileDrawerMenu } from "./MobileDrawerMenu";
import { YamsooLogo } from "@/components/logo/YamsooLogo";
import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";
import { useUnreadMessages } from "@/components/networks/hooks/useUnreadMessages";
import { useNotifications } from "@/hooks/useNotifications";
import { supabase } from "@/integrations/supabase/client";
import { useSuggestionCount } from "@/hooks/useSuggestionCount";
import { useProfile } from "@/hooks/useProfile";

export function MobileNavBar() {
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [userId, setUserId] = useState<string | null>(null);
  const unreadMessages = useUnreadMessages(userId || undefined);
  const { notifications } = useNotifications();
  const pendingNotifications = notifications?.filter(n => n.type === 'relation').length || 0;
  const { profile } = useProfile();

  // Convert profile to NotificationProfile type needed for useSuggestionCount
  const notificationProfile = profile ? {
    id: profile.id || "",
    first_name: profile.first_name,
    last_name: profile.last_name,
    avatar_url: profile.avatar_url,
    gender: profile.gender,
    email: profile.email
  } : null;

  const { suggestionCount } = useSuggestionCount(notificationProfile);

  useEffect(() => {
    // Fetch the current user ID for notifications
    const getUserId = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (user) {
        setUserId(user.id);
      }
    };

    getUserId();
  }, []);

  const navItems = [
    { icon: <Home size={20} />, label: "Accueil", path: "/dashboard", badge: 0 },
    { icon: <Trees size={20} />, label: "Famille", path: "/famille", badge: 0 },
    { icon: <Network size={20} />, label: "Réseaux", path: "/networks", badge: 0 },
    { icon: <Sparkles size={20} />, label: "Suggestions", path: "/suggestions", badge: suggestionCount },
    { icon: <Bell size={20} />, label: "Notifs", path: "/notifications", badge: pendingNotifications },
  ];

  const isActive = (path: string) => {
    return window.location.pathname === path;
  };

  // Don't show page title for specific routes
  const shouldShowPageTitle = () => {
    const currentPath = window.location.pathname;
    // List of pages where we don't want to show the title in navbar
    const pagesWithoutTitle = ['/networks', '/famille', '/famille/arbre'];
    return !pagesWithoutTitle.includes(currentPath);
  };

  return (
    <>
      <div className="fixed top-0 left-0 right-0 h-14 px-2 z-30 bg-background border-b flex items-center justify-between safe-top">
        <div className="flex items-center gap-3">
          <Button variant="ghost" size="icon" onClick={() => setDrawerOpen(true)}>
            <Menu size={24} />
          </Button>
          <YamsooLogo size={28} />
        </div>
        {shouldShowPageTitle() && (
          <h1 className="text-sm font-medium">
            {navItems.find(item => isActive(item.path))?.label || "Yamsoo"}
          </h1>
        )}
        <div className="w-9"></div>
      </div>

      <div className="fixed bottom-0 left-0 right-0 h-16 z-30 bg-background border-t flex items-center justify-around px-1 safe-bottom">
        {navItems.map((item, index) => (
          <Button
            key={index}
            variant="ghost"
            size="sm"
            className={cn(
              "flex flex-col h-14 rounded-lg px-1 py-2 gap-0.5 relative",
              isActive(item.path) ? "text-primary bg-secondary/40" : "text-muted-foreground",
              item.path === "/suggestions" ? "text-amber-600" : ""
            )}
            onClick={() => window.location.href = item.path}
          >
            <div className="relative">
              {item.icon}
              {item.badge > 0 && (
                <Badge
                  variant="destructive"
                  className="absolute -top-2 -right-2 h-4 min-w-4 p-0 flex items-center justify-center text-[10px]"
                >
                  {item.badge > 99 ? '99+' : item.badge}
                </Badge>
              )}
            </div>
            <span className="text-xs font-normal">{item.label}</span>
          </Button>
        ))}
      </div>

      <MobileDrawerMenu open={drawerOpen} onOpenChange={setDrawerOpen} />
    </>
  );
}
