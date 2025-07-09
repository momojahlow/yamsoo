
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { LucideIcon } from "lucide-react";

interface MobileDrawerMenuItemProps {
  icon: React.ReactNode;
  label: string;
  path: string;
  count?: number;
  onClick: (path: string) => void;
  variant?: "default" | "highlight" | "special";
  hideCount?: boolean; // New prop to optionally hide count/badge
}

export function MobileDrawerMenuItem({ 
  icon, 
  label, 
  path, 
  count, 
  onClick,
  variant = "default",
  hideCount = false 
}: MobileDrawerMenuItemProps) {
  let buttonClasses = "w-full justify-start text-sm h-11 relative";
  
  if (variant === "highlight") {
    buttonClasses += " bg-amber-50 hover:bg-amber-100 text-amber-600";
  } else if (variant === "special") {
    buttonClasses += " bg-primary/10 hover:bg-primary/20";
  }
  
  return (
    <Button
      variant="ghost"
      className={buttonClasses}
      onClick={() => onClick(path)}
    >
      {icon}
      <span className={`ml-3 ${variant === "special" ? "font-medium" : ""}`}>{label}</span>
      {count !== undefined && count > 0 && !hideCount && (
        <Badge variant="destructive" className="ml-auto">
          {count}
        </Badge>
      )}
    </Button>
  );
}
