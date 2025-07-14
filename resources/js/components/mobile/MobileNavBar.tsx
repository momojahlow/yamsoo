import { MessageSquare, Trees, Bell, Home, Network, Sparkles } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Link } from "@inertiajs/react";

interface MobileNavBarProps {
  unreadMessages?: number;
  unreadNotifications?: number;
  suggestionCount?: number;
}

export function MobileNavBar({ 
  unreadMessages = 0, 
  unreadNotifications = 0, 
  suggestionCount = 0 
}: MobileNavBarProps = {}) {
  const navItems = [
    { icon: <Home size={20} />, label: "Accueil", path: "/dashboard", badge: 0 },
    { icon: <MessageSquare size={20} />, label: "Messages", path: "/messagerie", badge: unreadMessages },
    { icon: <Trees size={20} />, label: "Famille", path: "/famille", badge: 0 },
    { icon: <Network size={20} />, label: "Réseaux", path: "/networks", badge: 0 },
    { icon: <Sparkles size={20} />, label: "Suggestions", path: "/suggestions", badge: suggestionCount },
    { icon: <Bell size={20} />, label: "Notifs", path: "/notifications", badge: unreadNotifications },
  ];

  const isActive = (path: string) => {
    return window.location.pathname === path;
  };

  return (
    <div className="fixed bottom-0 left-0 right-0 h-16 z-30 bg-background border-t flex items-center justify-around px-1 safe-bottom md:hidden">
      {navItems.map((item, index) => (
        <Link
          key={index}
          href={item.path}
          className={`flex flex-col items-center gap-1 h-14 px-2 relative rounded-lg hover:bg-accent transition-colors ${
            isActive(item.path) ? "text-primary" : "text-muted-foreground"
          }`}
        >
          {item.icon}
          <span className="text-xs">{item.label}</span>
          {item.badge > 0 && (
            <Badge 
              variant="destructive" 
              className="absolute -top-1 -right-1 h-5 w-5 text-xs p-0 flex items-center justify-center"
            >
              {item.badge > 99 ? "99+" : item.badge}
            </Badge>
          )}
        </Link>
      ))}
    </div>
  );
}
