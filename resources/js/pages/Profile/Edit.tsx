import { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import { ProfileAvatar } from "@/components/profile/ProfileAvatar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { FormField } from "@/components/form/FormField";
import { GenderField } from "@/components/form/GenderField";
import { useIsMobile } from "@/hooks/use-mobile";
import { useToast } from "@/hooks/use-toast";
import AppLayout from '@/layouts/app-layout';
import {
  User,
  Save,
  ArrowLeft
} from "lucide-react";
import { Link } from "@inertiajs/react";

interface Profile {
  id?: number;
  first_name?: string;
  last_name?: string;
  bio?: string;
  avatar?: string;
  email?: string;
  mobile?: string;
  birth_date?: string;
  gender?: string;
  avatar_url?: string | null;
}

interface ProfileEditPageProps {
  profile: Profile | null;
}

const ProfileEditPage = ({ profile }: ProfileEditPageProps) => {
  const isMobile = useIsMobile();
  const { toast } = useToast();
  const [avatarUploading, setAvatarUploading] = useState(false);

  // Helper function to convert ISO date to yyyy-MM-dd format
  const formatDateForInput = (dateString: string | null | undefined): string => {
    if (!dateString) return "";
    try {
      const date = new Date(dateString);
      return date.toISOString().split('T')[0];
    } catch {
      return "";
    }
  };

  const { data, setData, patch, processing, errors } = useForm({
    first_name: profile?.first_name || "",
    last_name: profile?.last_name || "",
    email: profile?.email || "",
    mobile: profile?.mobile || "",
    birth_date: formatDateForInput(profile?.birth_date),
    gender: profile?.gender || "",
    bio: profile?.bio || "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    patch(route('profile.update'), {
      onSuccess: () => {
        toast({
          title: "Profil mis à jour",
          description: "Vos informations ont été sauvegardées avec succès.",
        });
      },
      onError: () => {
        toast({
          title: "Erreur",
          description: "Une erreur est survenue lors de la mise à jour.",
          variant: "destructive",
        });
      },
    });
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setData(e.target.name as keyof typeof data, e.target.value);
  };

  const handleGenderChange = (value: string) => {
    setData('gender', value);
  };

  const handleAvatarUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    // TODO: Implement avatar upload functionality
    console.log('Avatar upload:', e.target.files);
  };

  const breadcrumbs = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Mon Profil', href: '/profil' },
    { label: 'Modifier', href: '/profil/edit' }
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Modifier le Profil" />
      
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div className="max-w-4xl mx-auto p-6 md:p-8">
          {/* Header */}
          <div className="mb-8">
            <div className="flex items-center justify-between">
              <div>
                <div className="flex items-center space-x-4 mb-2">
                  <Link href={route('profile.index')}>
                    <Button variant="outline" size="sm">
                      <ArrowLeft className="w-4 h-4 mr-2" />
                      Retour
                    </Button>
                  </Link>
                </div>
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                  Modifier mon Profil
                </h1>
                <p className="text-gray-600 dark:text-gray-400 mt-1">
                  Mettez à jour vos informations personnelles
                </p>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Profile Edit Form */}
            <div className="lg:col-span-2">
              <Card className="border-0 shadow-sm">
                <CardHeader>
                  <CardTitle className="flex items-center">
                    <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                      <User className="w-4 h-4 text-blue-600" />
                    </div>
                    Informations personnelles
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  {/* Avatar Section */}
                  <div className="flex items-start space-x-4 mb-6">
                    <ProfileAvatar
                      avatarUrl={profile?.avatar_url || profile?.avatar || null}
                      firstName={data.first_name}
                      lastName={data.last_name}
                      uploading={avatarUploading}
                      onAvatarUpload={handleAvatarUpload}
                    />
                    <div className="flex-1">
                      <h3 className="text-lg font-semibold">
                        {data.first_name && data.last_name
                          ? `${data.first_name} ${data.last_name}`
                          : "Nom non renseigné"}
                      </h3>
                      <p className="text-gray-600 dark:text-gray-400 text-sm">
                        Cliquez sur l'avatar pour le modifier
                      </p>
                    </div>
                  </div>

                  {/* Profile Form */}
                  <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <FormField
                        label="Prénom"
                        id="first_name"
                        name="first_name"
                        value={data.first_name}
                        onChange={handleInputChange}
                        error={errors.first_name}
                        required
                      />
                      <FormField
                        label="Nom"
                        id="last_name"
                        name="last_name"
                        value={data.last_name}
                        onChange={handleInputChange}
                        error={errors.last_name}
                        required
                      />
                    </div>
                    
                    <FormField
                      label="Email"
                      id="email"
                      name="email"
                      type="email"
                      value={data.email}
                      onChange={handleInputChange}
                      error={errors.email}
                      required
                    />
                    
                    <FormField
                      label="Téléphone"
                      id="mobile"
                      name="mobile"
                      value={data.mobile}
                      onChange={handleInputChange}
                      error={errors.mobile}
                    />
                    
                    <FormField
                      label="Date de naissance"
                      id="birth_date"
                      name="birth_date"
                      type="date"
                      value={data.birth_date}
                      onChange={handleInputChange}
                      error={errors.birth_date}
                    />
                    
                    <GenderField
                      value={data.gender}
                      onChange={handleGenderChange}
                    />
                    
                    <FormField
                      label="Bio"
                      id="bio"
                      name="bio"
                      value={data.bio}
                      onChange={handleInputChange}
                      error={errors.bio}
                      multiline
                      placeholder="Parlez-nous de vous..."
                    />

                    <div className="flex space-x-4 pt-6">
                      <Link href={route('profile.index')} className="flex-1">
                        <Button variant="outline" className="w-full">
                          Annuler
                        </Button>
                      </Link>
                      <Button type="submit" disabled={processing} className="flex-1">
                        <Save className="w-4 h-4 mr-2" />
                        {processing ? 'Sauvegarde...' : 'Sauvegarder'}
                      </Button>
                    </div>
                  </form>
                </CardContent>
              </Card>
            </div>

            {/* Help Sidebar */}
            <div>
              <Card className="border-0 shadow-sm">
                <CardHeader>
                  <CardTitle className="text-lg">Conseils</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                    <div>
                      <h4 className="font-medium text-gray-900 dark:text-white mb-1">
                        Photo de profil
                      </h4>
                      <p>
                        Utilisez une photo claire de votre visage pour que votre famille puisse vous reconnaître facilement.
                      </p>
                    </div>
                    <div>
                      <h4 className="font-medium text-gray-900 dark:text-white mb-1">
                        Informations personnelles
                      </h4>
                      <p>
                        Remplissez vos informations pour aider votre famille à mieux vous connaître et vous contacter.
                      </p>
                    </div>
                    <div>
                      <h4 className="font-medium text-gray-900 dark:text-white mb-1">
                        Confidentialité
                      </h4>
                      <p>
                        Vos informations ne sont visibles que par les membres de votre famille connectés.
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
};

export default ProfileEditPage;
