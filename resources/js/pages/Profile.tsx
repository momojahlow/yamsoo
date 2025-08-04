import { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import { ProfileAvatar } from "@/components/profile/ProfileAvatar";
import { AvatarUpload } from "@/components/profile/AvatarUpload";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { FormField } from "@/components/form/FormField";
import { GenderField } from "@/components/form/GenderField";
import { useTranslation } from "react-i18next";
import { useIsMobile } from "@/hooks/use-mobile";
import { useToast } from "@/hooks/use-toast";
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import {
  User,
  Mail,
  Phone,
  Calendar,
  MapPin,
  Edit3,
  Save,
  X
} from "lucide-react";

interface User {
  id: number;
  name: string;
  email: string;
}

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

interface ProfilePageProps {
  user: User;
  profile: Profile | null;
}

const ProfilePage = ({ user, profile }: ProfilePageProps) => {
  const { t } = useTranslation();
  const isMobile = useIsMobile();
  const { toast } = useToast();
  const [isEditing, setIsEditing] = useState(false);
  const [avatarUploading, setAvatarUploading] = useState(false);

  const { data, setData, patch, processing, errors, reset } = useForm({
    first_name: profile?.first_name || "",
    last_name: profile?.last_name || "",
    email: profile?.email || user.email,
    mobile: profile?.mobile || "",
    birth_date: profile?.birth_date || "",
    gender: profile?.gender || "",
    bio: profile?.bio || "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    patch(route('profile.update'), {
      onSuccess: () => {
        setIsEditing(false);
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

  const handleCancel = () => {
    reset();
    setIsEditing(false);
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
    { label: 'Mon Profil', href: '/profil' }
  ];

  return (
    <AppSidebarLayout breadcrumbs={breadcrumbs}>
      <Head title="Mon Profil" />

      <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
        <div className="w-full max-w-6xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
          {/* Header responsive */}
          <div className="mb-6 sm:mb-8 md:mb-12">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div className="text-center sm:text-left">
                <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-tight">
                  Mon Profil
                </h1>
                <p className="text-gray-600 mt-2 sm:mt-3 text-xs sm:text-sm md:text-base max-w-2xl mx-auto sm:mx-0 leading-relaxed">
                  Gérez vos informations personnelles
                </p>
              </div>

              {/* Boutons d'action responsive */}
              <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
                {!isEditing ? (
                  <Button
                    onClick={() => setIsEditing(true)}
                    className="w-full sm:w-auto h-9 sm:h-10 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl text-sm"
                  >
                    <Edit3 className="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                    Modifier
                  </Button>
                ) : (
                  <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <Button
                      variant="outline"
                      onClick={handleCancel}
                      className="w-full sm:w-auto h-9 sm:h-10 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 text-sm"
                    >
                      <X className="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                      Annuler
                    </Button>
                    <Button
                      onClick={handleSubmit}
                      disabled={processing}
                      className="w-full sm:w-auto h-9 sm:h-10 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl text-sm"
                    >
                      <Save className="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                      Sauvegarder
                    </Button>
                  </div>
                )}
              </div>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
            {/* Profile Info Card */}
            <div className="lg:col-span-2">
              <Card className="border-0 shadow-sm bg-gradient-to-br from-white to-gray-50/50">
                <CardHeader className="p-4 sm:p-6">
                  <CardTitle className="flex items-center justify-between text-base sm:text-lg md:text-xl">
                    <div className="flex items-center">
                      <div className="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                        <User className="w-3 h-3 sm:w-4 sm:h-4 text-white" />
                      </div>
                      <span className="text-sm sm:text-base md:text-lg">Informations personnelles</span>
                    </div>
                    {isMobile && !isEditing && (
                      <Button size="sm" onClick={() => setIsEditing(true)} className="h-8 w-8 p-0">
                        <Edit3 className="w-3 h-3" />
                      </Button>
                    )}
                  </CardTitle>
                </CardHeader>
                <CardContent className="p-4 sm:p-6 pt-0">
                  {/* Avatar Section responsive */}
                  <div className="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mb-6">
                    <div className="flex-shrink-0">
                      <AvatarUpload
                        avatarUrl={profile?.avatar_url || profile?.avatar || null}
                        firstName={data.first_name}
                        lastName={data.last_name}
                      />
                    </div>
                    <div className="flex-1 text-center sm:text-left">
                      <h3 className="text-base sm:text-lg font-semibold text-gray-900">
                        {data.first_name && data.last_name
                          ? `${data.first_name} ${data.last_name}`
                          : user?.name || "Utilisateur"}
                      </h3>
                      <p className="text-gray-600 text-xs sm:text-sm mt-1 leading-relaxed">
                        {data.bio || "Aucune bio disponible"}
                      </p>
                      <div className="flex flex-wrap items-center justify-center sm:justify-start mt-2 gap-2">
                        <Badge variant="secondary" className="text-xs px-2 py-1">Membre actif</Badge>
                        <Badge variant="outline" className="text-xs px-2 py-1">Famille connectée</Badge>
                      </div>
                    </div>
                  </div>

                  {/* Profile Form responsive */}
                  {isEditing ? (
                    <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-6">
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <FormField
                          label="Prénom"
                          id="first_name"
                          name="first_name"
                          value={data.first_name}
                          onChange={handleInputChange}
                          error={errors.first_name}
                        />
                        <FormField
                          label="Nom"
                          id="last_name"
                          name="last_name"
                          value={data.last_name}
                          onChange={handleInputChange}
                          error={errors.last_name}
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
                      />

                      {isMobile && (
                        <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4">
                          <Button
                            variant="outline"
                            onClick={handleCancel}
                            className="w-full h-10 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 text-sm"
                          >
                            <X className="w-3 h-3 mr-2" />
                            Annuler
                          </Button>
                          <Button
                            type="submit"
                            disabled={processing}
                            className="w-full h-10 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl text-sm"
                          >
                            <Save className="w-3 h-3 mr-2" />
                            Sauvegarder
                          </Button>
                        </div>
                      )}
                    </form>
                  ) : (
                    <div className="space-y-3 sm:space-y-4">
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div className="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 bg-gray-50 rounded-lg">
                          <Mail className="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 flex-shrink-0" />
                          <span className="text-xs sm:text-sm text-gray-700 truncate">{data.email || "Non renseigné"}</span>
                        </div>
                        <div className="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 bg-gray-50 rounded-lg">
                          <Phone className="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 flex-shrink-0" />
                          <span className="text-xs sm:text-sm text-gray-700">{data.mobile || "Non renseigné"}</span>
                        </div>
                        <div className="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 bg-gray-50 rounded-lg">
                          <Calendar className="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 flex-shrink-0" />
                          <span className="text-xs sm:text-sm text-gray-700">
                            {data.birth_date
                              ? new Date(data.birth_date).toLocaleDateString('fr-FR', {
                                  year: 'numeric',
                                  month: 'long',
                                  day: 'numeric'
                                })
                              : "Non renseigné"
                            }
                          </span>
                        </div>
                        <div className="flex items-center space-x-3">
                          <User className="w-4 h-4 text-gray-400" />
                          <span className="text-sm">
                            {data.gender === 'male' ? 'Homme' : data.gender === 'female' ? 'Femme' : 'Non renseigné'}
                          </span>
                        </div>
                      </div>
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>

            {/* Stats Sidebar */}
            <div>
              <Card className="border-0 shadow-sm">
                <CardHeader>
                  <CardTitle className="text-lg">Statistiques</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div className="flex justify-between items-center">
                      <span className="text-sm text-gray-600">Profil complété</span>
                      <span className="text-sm font-medium">85%</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span className="text-sm text-gray-600">Membre depuis</span>
                      <span className="text-sm font-medium">2024</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span className="text-sm text-gray-600">Dernière connexion</span>
                      <span className="text-sm font-medium">Aujourd'hui</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </AppSidebarLayout>
  );
};

export default ProfilePage;
