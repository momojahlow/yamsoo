import { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import { ProfileAvatar } from "@/components/profile/ProfileAvatar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { FormField } from "@/components/form/FormField";
import { GenderField } from "@/components/form/GenderField";
import { useTranslation } from "react-i18next";
import { useIsMobile } from "@/hooks/use-mobile";
import { useToast } from "@/hooks/use-toast";
import AppLayout from '@/layouts/app-layout';
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
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Mon Profil" />
      
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div className="max-w-4xl mx-auto p-6 md:p-8">
          {/* Header */}
          <div className="mb-8">
            <div className="flex items-center justify-between">
              <div>
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                  Mon Profil
                </h1>
                <p className="text-gray-600 dark:text-gray-400 mt-1">
                  Gérez vos informations personnelles
                </p>
              </div>
              {!isEditing ? (
                <Button onClick={() => setIsEditing(true)} className="hidden md:flex">
                  <Edit3 className="w-4 h-4 mr-2" />
                  Modifier
                </Button>
              ) : (
                <div className="hidden md:flex space-x-2">
                  <Button variant="outline" onClick={handleCancel}>
                    <X className="w-4 h-4 mr-2" />
                    Annuler
                  </Button>
                  <Button onClick={handleSubmit} disabled={processing}>
                    <Save className="w-4 h-4 mr-2" />
                    Sauvegarder
                  </Button>
                </div>
              )}
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Profile Info Card */}
            <div className="lg:col-span-2">
              <Card className="border-0 shadow-sm">
                <CardHeader>
                  <CardTitle className="flex items-center justify-between">
                    <div className="flex items-center">
                      <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <User className="w-4 h-4 text-blue-600" />
                      </div>
                      Informations personnelles
                    </div>
                    {isMobile && !isEditing && (
                      <Button size="sm" onClick={() => setIsEditing(true)}>
                        <Edit3 className="w-4 h-4" />
                      </Button>
                    )}
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
                          : user?.name || "Utilisateur"}
                      </h3>
                      <p className="text-gray-600 dark:text-gray-400 text-sm">
                        {data.bio || "Aucune bio disponible"}
                      </p>
                      <div className="flex items-center mt-2 space-x-2">
                        <Badge variant="secondary">Membre actif</Badge>
                        <Badge variant="outline">Famille connectée</Badge>
                      </div>
                    </div>
                  </div>

                  {/* Profile Form */}
                  {isEditing ? (
                    <form onSubmit={handleSubmit} className="space-y-6">
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <div className="flex space-x-2">
                          <Button variant="outline" onClick={handleCancel} className="flex-1">
                            Annuler
                          </Button>
                          <Button type="submit" disabled={processing} className="flex-1">
                            Sauvegarder
                          </Button>
                        </div>
                      )}
                    </form>
                  ) : (
                    <div className="space-y-4">
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="flex items-center space-x-3">
                          <Mail className="w-4 h-4 text-gray-400" />
                          <span className="text-sm">{data.email || "Non renseigné"}</span>
                        </div>
                        <div className="flex items-center space-x-3">
                          <Phone className="w-4 h-4 text-gray-400" />
                          <span className="text-sm">{data.mobile || "Non renseigné"}</span>
                        </div>
                        <div className="flex items-center space-x-3">
                          <Calendar className="w-4 h-4 text-gray-400" />
                          <span className="text-sm">{data.birth_date || "Non renseigné"}</span>
                        </div>
                        <div className="flex items-center space-x-3">
                          <User className="w-4 h-4 text-gray-400" />
                          <span className="text-sm">
                            {data.gender === 'M' ? 'Homme' : data.gender === 'F' ? 'Femme' : 'Non renseigné'}
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
    </AppLayout>
  );
};

export default ProfilePage;
