
import { Separator } from "@/components/ui/separator";
import { MobileDrawerMenuItem } from "./MobileDrawerMenuItem";
import {
  Calendar,
  MessageSquare,
  Network,
  Trees,
  Home,
  Bell,
  Sparkles,
  User
} from "lucide-react";

interface MobileDrawerMenuItemsProps {
  onNavigation: (path: string) => void;
  suggestionCount: number;
}

export function MobileDrawerMenuItems({ onNavigation, suggestionCount }: MobileDrawerMenuItemsProps) {
  return (
    <div className="px-4 py-2">
      <Separator className="my-3" />

      <div className="flex flex-col gap-1 mt-4">
        <MobileDrawerMenuItem
          icon={<Home className="h-5 w-5" />}
          label="Accueil"
          path="/dashboard"
          onClick={onNavigation}
        />

        <MobileDrawerMenuItem
          icon={<User className="h-5 w-5" />}
          label="Mon Profil"
          path="/profil"
          onClick={onNavigation}
        />

        <MobileDrawerMenuItem
          icon={<Trees className="h-5 w-5" />}
          label="Famille"
          path="/famille"
          onClick={onNavigation}
        />

        <MobileDrawerMenuItem
          icon={<Network className="h-5 w-5" />}
          label="Réseaux"
          path="/reseaux"
          onClick={onNavigation}
        />

        <MobileDrawerMenuItem
          icon={<MessageSquare className="h-5 w-5" />}
          label="Messages"
          path="/messagerie"
          onClick={onNavigation}
        />

        <MobileDrawerMenuItem
          icon={<Bell className="h-5 w-5" />}
          label="Notifications"
          path="/notifications"
          onClick={onNavigation}
        />

        <MobileDrawerMenuItem
          icon={<Sparkles className="h-5 w-5" />}
          label="Suggestions"
          path="/suggestions"
          onClick={onNavigation}
          count={suggestionCount}
          variant="highlight"
        />

        {/* Add the family tree button with a different style to make it stand out */}
        <MobileDrawerMenuItem
          icon={<Trees className="h-5 w-5" />}
          label="Arbre Familial"
          path="/famille/arbre"
          onClick={onNavigation}
          variant="special"
        />
      </div>

      <Separator className="my-3" />
    </div>
  );
}
