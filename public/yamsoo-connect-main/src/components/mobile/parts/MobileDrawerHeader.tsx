
import { DrawerHeader, DrawerTitle } from "@/components/ui/drawer";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { YamsooLogo } from "@/components/logo/YamsooLogo";
import { Profile } from "@/types/notifications";

interface MobileDrawerHeaderProps {
  profile: Profile | null;
}

export function MobileDrawerHeader({ profile }: MobileDrawerHeaderProps) {
  // Create initials from profile name
  const firstInitial = profile?.first_name?.[0] || '';
  const lastInitial = profile?.last_name?.[0] || '';
  const initials = `${firstInitial}${lastInitial}`.toUpperCase();
  
  return (
    <>
      <DrawerHeader className="text-left flex items-center gap-3">
        <YamsooLogo size={32} />
        <DrawerTitle>Yamsoo</DrawerTitle>
      </DrawerHeader>

      <div className="px-4 py-2">
        <div className="flex items-center gap-3 mb-4">
          <Avatar className="h-12 w-12 border-2 border-primary">
            <AvatarImage src={profile?.avatar_url || ""} />
            <AvatarFallback className="bg-slate-100 text-slate-500">
              {initials}
            </AvatarFallback>
          </Avatar>
          <div>
            <p className="font-medium text-sm">{profile?.first_name} {profile?.last_name}</p>
            <p className="text-xs text-muted-foreground">{profile?.email}</p>
          </div>
        </div>
      </div>
    </>
  );
}
