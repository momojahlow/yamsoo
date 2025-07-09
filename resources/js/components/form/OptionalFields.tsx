
import { useTranslation } from "react-i18next";
import { FormField } from "./FormField";
import { GenderField } from "./GenderField";

interface OptionalFieldsProps {
  birthDate: string;
  gender: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  onGenderChange: (value: string) => void;
  errors: Record<string, string>;
}

export const OptionalFields = ({ birthDate, gender, onChange, onGenderChange, errors }: OptionalFieldsProps) => {
  const { t } = useTranslation();

  return (
    <>
      <FormField
        label={t("form.birthDate")}
        id="birthDate"
        name="birthDate"
        type="date"
        value={birthDate}
        onChange={onChange}
        required={false}
        error={errors.birthDate}
      />

      <GenderField
        value={gender}
        onChange={onGenderChange}
        error={errors.gender}
      />
    </>
  );
};
