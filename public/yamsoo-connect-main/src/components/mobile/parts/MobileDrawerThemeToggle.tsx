
import { useState } from "react";
import { Switch } from "@/components/ui/switch";
import { Label } from "@/components/ui/label";
import { Moon, Sun } from "lucide-react";

export function MobileDrawerThemeToggle() {
  const [darkMode, setDarkMode] = useState(false);
  
  return (
    <div className="flex items-center space-x-2 px-2 py-2">
      <Switch
        id="dark-mode"
        checked={darkMode}
        onCheckedChange={setDarkMode}
      />
      <Label htmlFor="dark-mode" className="flex items-center gap-2">
        {darkMode ? (
          <Moon className="h-4 w-4" />
        ) : (
          <Sun className="h-4 w-4" />
        )}
        <span>Mode {darkMode ? "sombre" : "clair"}</span>
      </Label>
    </div>
  );
}
