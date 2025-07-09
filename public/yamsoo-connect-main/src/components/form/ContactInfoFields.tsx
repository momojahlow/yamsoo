
import { useTranslation } from "react-i18next";
import { FormField } from "./FormField";

interface ContactInfoFieldsProps {
  email: string;
  mobile: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  errors: Record<string, string>;
}

export const ContactInfoFields = ({ email, mobile, onChange, errors }: ContactInfoFieldsProps) => {
  const { t } = useTranslation();

  return (
    <>
      <FormField
        label={t("form.email")}
        id="email"
        name="email"
        type="email"
        value={email}
        onChange={onChange}
        required
        error={errors.email}
      />

      <FormField
        label={t("form.mobile")}
        id="mobile"
        name="mobile"
        type="tel"
        value={mobile}
        onChange={onChange}
        required={false}
        error={errors.mobile}
      />
    </>
  );
};
