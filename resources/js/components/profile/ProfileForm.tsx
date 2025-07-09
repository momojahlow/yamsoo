
import { Button } from "@/components/ui/button";
import { FormField } from "@/components/form/FormField";
import { GenderField } from "@/components/form/GenderField";
import { useTranslation } from "react-i18next";
import { useIsMobile } from "@/hooks/use-mobile";

interface Profile {
  first_name: string;
  last_name: string;
  email: string;
  mobile: string;
  birth_date: string;
  gender: string;
  avatar_url: string | null;
}

interface ProfileFormProps {
  profile: Profile;
  onSubmit: (e: React.FormEvent) => void;
  onInputChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  onGenderChange: (value: string) => void;
}

export const ProfileForm = ({
  profile,
  onSubmit,
  onInputChange,
  onGenderChange,
}: ProfileFormProps) => {
  const { t } = useTranslation();
  const isMobile = useIsMobile();

  return (
    <form onSubmit={onSubmit} className={`space-y-${isMobile ? '4' : '6'} bg-white p-${isMobile ? '4' : '6'} rounded-lg shadow`}>
      <FormField
        label={t("form.firstName")}
        id="first_name"
        name="first_name"
        value={profile.first_name || ""}
        onChange={onInputChange}
      />
      
      <FormField
        label={t("form.lastName")}
        id="last_name"
        name="last_name"
        value={profile.last_name || ""}
        onChange={onInputChange}
      />
      
      <FormField
        label={t("form.email")}
        id="email"
        name="email"
        type="email"
        value={profile.email || ""}
        onChange={onInputChange}
      />
      
      <FormField
        label={t("form.mobile")}
        id="mobile"
        name="mobile"
        value={profile.mobile || ""}
        onChange={onInputChange}
      />
      
      <FormField
        label={t("form.birthDate")}
        id="birth_date"
        name="birth_date"
        type="date"
        value={profile.birth_date || ""}
        onChange={onInputChange}
      />
      
      <GenderField
        value={profile.gender || ""}
        onChange={onGenderChange}
      />
      
      <Button type="submit" className="w-full">
        {t("profile.save")}
      </Button>
    </form>
  );
};
