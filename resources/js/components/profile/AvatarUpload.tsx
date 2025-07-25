import { useState } from "react";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Camera, Upload, Loader2 } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { router } from "@inertiajs/react";

interface AvatarUploadProps {
  avatarUrl: string | null;
  firstName: string;
  lastName: string;
  onAvatarUpdated?: (newAvatarUrl: string) => void;
}

export const AvatarUpload = ({
  avatarUrl,
  firstName,
  lastName,
  onAvatarUpdated,
}: AvatarUploadProps) => {
  const [uploading, setUploading] = useState(false);
  const { toast } = useToast();

  // Create initials from first and last name
  const firstInitial = firstName?.[0] || '';
  const lastInitial = lastName?.[0] || '';
  const initials = `${firstInitial}${lastInitial}`.toUpperCase();

  const handleAvatarUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
      toast({
        title: "Erreur",
        description: "L'image ne doit pas dépasser 2MB.",
        variant: "destructive",
      });
      return;
    }

    // Validate file type
    if (!file.type.startsWith('image/')) {
      toast({
        title: "Erreur",
        description: "Veuillez sélectionner une image valide.",
        variant: "destructive",
      });
      return;
    }

    setUploading(true);

    try {
      const formData = new FormData();
      formData.append('avatar', file);

      const response = await fetch('/profil/avatar', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      const result = await response.json();

      if (result.success) {
        toast({
          title: "Succès",
          description: "Avatar mis à jour avec succès.",
        });
        
        // Call the callback if provided
        if (onAvatarUpdated) {
          onAvatarUpdated(result.avatar_url);
        }
        
        // Refresh the page to show the new avatar
        router.reload();
      } else {
        throw new Error(result.message || 'Erreur lors de la mise à jour');
      }
    } catch (error) {
      console.error('Avatar upload error:', error);
      toast({
        title: "Erreur",
        description: "Impossible de mettre à jour l'avatar.",
        variant: "destructive",
      });
    } finally {
      setUploading(false);
      // Reset the input
      e.target.value = '';
    }
  };

  return (
    <div className="flex flex-col items-center space-y-4">
      <Avatar className="h-24 w-24">
        <AvatarImage src={avatarUrl || undefined} />
        <AvatarFallback className="bg-slate-100 text-slate-500 text-xl">
          {initials}
        </AvatarFallback>
      </Avatar>
      
      <div className="flex flex-col items-center space-y-2">
        <div className="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            onClick={() => document.getElementById('camera-input')?.click()}
            disabled={uploading}
            className="flex items-center gap-2"
          >
            {uploading ? (
              <Loader2 className="h-4 w-4 animate-spin" />
            ) : (
              <Camera className="h-4 w-4" />
            )}
            Caméra
          </Button>

          <Button
            variant="outline"
            size="sm"
            onClick={() => document.getElementById('file-input')?.click()}
            disabled={uploading}
            className="flex items-center gap-2"
          >
            {uploading ? (
              <Loader2 className="h-4 w-4 animate-spin" />
            ) : (
              <Upload className="h-4 w-4" />
            )}
            Fichier
          </Button>
        </div>
        
        <input
          id="camera-input"
          type="file"
          accept="image/*"
          capture="environment"
          onChange={handleAvatarUpload}
          disabled={uploading}
          className="hidden"
        />
        
        <input
          id="file-input"
          type="file"
          accept="image/*"
          onChange={handleAvatarUpload}
          disabled={uploading}
          className="hidden"
        />
      </div>
    </div>
  );
};
