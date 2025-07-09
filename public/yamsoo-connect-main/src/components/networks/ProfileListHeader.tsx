
import { Button } from "@/components/ui/button";

interface ProfileListHeaderProps {
  title?: string;
}

export const ProfileListHeader = ({ title = "Utilisateurs disponibles" }: ProfileListHeaderProps) => {
  return (
    <div className="flex justify-between items-center">
      <h2 className="text-xl font-semibold text-brown">{title}</h2>
    </div>
  );
};
