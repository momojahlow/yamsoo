
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Label } from "@/components/ui/label";
import { Camera, Upload } from "lucide-react";
import { useTranslation } from "react-i18next";
import { Button } from "@/components/ui/button";
import { useIsMobile } from "@/hooks/use-mobile";

interface ProfileAvatarProps {
  avatarUrl: string | null;
  firstName: string;
  lastName: string;
  uploading: boolean;
  onAvatarUpload: (e: React.ChangeEvent<HTMLInputElement>) => void;
}

export const ProfileAvatar = ({
  avatarUrl,
  firstName,
  lastName,
  uploading,
  onAvatarUpload,
}: ProfileAvatarProps) => {
  const { t } = useTranslation();
  const isMobile = useIsMobile();

  // Create initials from first and last name
  const firstInitial = firstName?.[0] || '';
  const lastInitial = lastName?.[0] || '';
  const initials = `${firstInitial}${lastInitial}`.toUpperCase();

  return (
    <div className="mb-8 flex flex-col items-center space-y-4">
      <Avatar className="h-24 w-24">
        <AvatarImage src={avatarUrl || undefined} />
        <AvatarFallback className="bg-slate-100 text-slate-500 text-xl">
          {initials}
        </AvatarFallback>
      </Avatar>
      
      <div className="flex flex-col items-center space-y-2">
        {isMobile ? (
          <div className="flex w-full gap-4 mt-2 justify-center">
            <button 
              onClick={() => document.getElementById('camera-input')?.click()}
              disabled={uploading}
              className="flex items-center justify-center bg-slate-100 rounded-full p-3 hover:bg-slate-200 transition-colors"
            >
              <Camera size={20} className="text-slate-700" />
            </button>

            <button 
              onClick={() => document.getElementById('file-input')?.click()}
              disabled={uploading}
              className="flex items-center justify-center bg-slate-100 rounded-full p-3 hover:bg-slate-200 transition-colors"
            >
              <Upload size={20} className="text-slate-700" />
            </button>
            
            <input
              id="camera-input"
              type="file"
              accept="image/*"
              capture="environment"
              onChange={onAvatarUpload}
              disabled={uploading}
              className="hidden"
            />
            
            <input
              id="file-input"
              type="file"
              accept="image/*"
              onChange={onAvatarUpload}
              disabled={uploading}
              className="hidden"
            />
          </div>
        ) : (
          <>
            <Label htmlFor="avatar" className="cursor-pointer px-4 py-2 border rounded-md hover:bg-gray-50">
              {uploading ? t("profile.uploading") : t("profile.changePhoto")}
            </Label>
            <input
              id="avatar"
              type="file"
              accept="image/*"
              onChange={onAvatarUpload}
              disabled={uploading}
              className="hidden"
            />
          </>
        )}
      </div>
    </div>
  );
};
