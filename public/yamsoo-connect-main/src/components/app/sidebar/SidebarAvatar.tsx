
import { useNavigate } from "react-router-dom";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Profile } from "@/types/notifications";
import { useSidebar } from "@/components/ui/sidebar";

interface SidebarAvatarProps {
  profile: Profile | null;
}

export function SidebarAvatar({ profile }: SidebarAvatarProps) {
  const navigate = useNavigate();
  const { state } = useSidebar();
  const isCollapsed = state === 'collapsed';

  const navigateToProfile = () => {
    navigate("/dashboard");
  };

  // Create initials from first and last name
  const initials = profile ? 
    `${profile.first_name?.[0] || ''}${profile.last_name?.[0] || ''}`.toUpperCase() 
    : '';

  return (
    <div 
      className={`flex justify-center py-3 cursor-pointer ${isCollapsed ? 'px-2' : 'px-4'}`}
      onClick={navigateToProfile}
      title="Accéder à l'accueil"
    >
      <Avatar className="h-10 w-10 transition-transform duration-200 hover:scale-110">
        <AvatarImage src={profile?.avatar_url || ""} />
        <AvatarFallback className="bg-slate-100 text-slate-500">
          {initials}
        </AvatarFallback>
      </Avatar>
    </div>
  );
}
