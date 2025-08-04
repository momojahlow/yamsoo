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
      
      <div className="min-h-screen bg-gradient-to-br from-gray-50 to-white">
        <div className="w-full max-w-6xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
          {/* Header responsive */}
          <div className="mb-6 sm:mb-8 md:mb-12">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div className="text-center sm:text-left">
                <div className="flex items-center justify-center sm:justify-start space-x-4 mb-3 sm:mb-2">
                  <Link href={route('profile.index')}>
                    <Button variant="outline" size="sm" className="h-9 text-sm">
                      <ArrowLeft className="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                      Retour
                    </Button>
                  </Link>
                </div>
                <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-tight">
                  Modifier mon Profil
                </h1>
                <p className="text-gray-600 mt-2 sm:mt-3 text-xs sm:text-sm md:text-base max-w-2xl mx-auto sm:mx-0 leading-relaxed">
                  Mettez à jour vos informations personnelles
                </p>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
            {/* Profile Edit Form */}
            <div className="lg:col-span-2">
              <Card className="border-0 shadow-sm bg-gradient-to-br from-white to-gray-50/50">
                <CardHeader className="p-4 sm:p-6">
                  <CardTitle className="flex items-center text-base sm:text-lg md:text-xl">
                    <div className="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                      <User className="w-3 h-3 sm:w-4 sm:h-4 text-white" />
                    </div>
                    Informations personnelles
                  </CardTitle>
                </CardHeader>
                <CardContent className="p-4 sm:p-6 pt-0">
                  {/* Avatar Section responsive */}
                  <div className="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mb-6">
                    <ProfileAvatar
                      avatarUrl={profile?.avatar_url || profile?.avatar || null}
                      firstName={data.first_name}
                      lastName={data.last_name}
                      uploading={avatarUploading}
                      onAvatarUpload={handleAvatarUpload}
                    />
                    <div className="flex-1 text-center sm:text-left">
                      <h3 className="text-base sm:text-lg font-semibold text-gray-900">
                        {data.first_name && data.last_name
                          ? `${data.first_name} ${data.last_name}`
                          : "Nom non renseigné"}
                      </h3>
                      <p className="text-gray-600 text-xs sm:text-sm mt-1">
                        Cliquez sur l'avatar pour le modifier
                      </p>
                    </div>
                  </div>

                  {/* Profile Form responsive */}
                  <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-6">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
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

                    <div className="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-4 sm:pt-6">
                      <Link href={route('profile.index')} className="w-full sm:flex-1">
                        <Button variant="outline" className="w-full h-10 sm:h-11 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm">
                          Annuler
                        </Button>
                      </Link>
                      <Button type="submit" disabled={processing} className="w-full sm:flex-1 h-10 sm:h-11 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
                        <Save className="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                        {processing ? 'Sauvegarde...' : 'Sauvegarder'}
                      </Button>
                    </div>
                  </form>
                </CardContent>
              </Card>
            </div>

            {/* Help Sidebar responsive */}
            <div className="mt-6 lg:mt-0">
              <Card className="border-0 shadow-sm bg-gradient-to-br from-white to-gray-50/50">
                <CardHeader className="p-4 sm:p-6">
                  <CardTitle className="text-base sm:text-lg font-semibold text-gray-900">💡 Conseils</CardTitle>
                </CardHeader>
                <CardContent className="p-4 sm:p-6 pt-0">
                  <div className="space-y-3 sm:space-y-4 text-xs sm:text-sm text-gray-600">
                    <div className="p-3 bg-blue-50 rounded-lg">
                      <h4 className="font-medium text-blue-900 mb-1 text-xs sm:text-sm">
                        📸 Photo de profil
                      </h4>
                      <p className="text-blue-700 leading-relaxed">
                        Utilisez une photo claire de votre visage pour que votre famille puisse vous reconnaître facilement.
                      </p>
                    </div>
                    <div className="p-3 bg-green-50 rounded-lg">
                      <h4 className="font-medium text-green-900 mb-1 text-xs sm:text-sm">
                        📝 Informations personnelles
                      </h4>
                      <p className="text-green-700 leading-relaxed">
                        Remplissez vos informations pour aider votre famille à mieux vous connaître et vous contacter.
                      </p>
                    </div>
                    <div className="p-3 bg-purple-50 rounded-lg">
                      <h4 className="font-medium text-purple-900 mb-1 text-xs sm:text-sm">
                        🔒 Confidentialité
                      </h4>
                      <p className="text-purple-700 leading-relaxed">
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
