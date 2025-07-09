
import { FormField } from "./FormField";
import { useTranslation } from "react-i18next";

interface PersonalInfoFieldsProps {
  firstName: string;
  lastName: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  errors: Record<string, string>;
}

export const PersonalInfoFields = ({ firstName, lastName, onChange, errors }: PersonalInfoFieldsProps) => {
  const { t } = useTranslation();

  return (
    <>
      <FormField
        label={t("form.firstName") + "*"}
        id="firstName"
        name="firstName"
        value={firstName}
        onChange={onChange}
        required
        error={errors.firstName}
      />

      <FormField
        label={t("form.lastName")}
        id="lastName"
        name="lastName"
        value={lastName}
        onChange={onChange}
        required
        error={errors.lastName}
      />
    </>
  );
};
