
import { FormField } from "./FormField";
import { useTranslation } from "react-i18next";

interface PasswordFieldsProps {
  password: string;
  confirmPassword: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  errors: Record<string, string>;
}

export const PasswordFields = ({ password, confirmPassword, onChange, errors }: PasswordFieldsProps) => {
  const { t } = useTranslation();
  
  return (
    <div className="space-y-4">
      <FormField
        label={t("auth.password")}
        id="password"
        name="password"
        type="password"
        value={password}
        onChange={onChange}
        required
        error={errors.password}
      />

      <FormField
        label={t("auth.confirmPassword")}
        id="confirmPassword"
        name="confirmPassword"
        type="password"
        value={confirmPassword}
        onChange={onChange}
        required
        error={errors.confirmPassword}
      />
    </div>
  );
};
