
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { useTranslation } from "react-i18next";

interface FormFieldProps {
  label: string;
  id: string;
  name: string;
  type?: string;
  value: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  required?: boolean;
  error?: string;
}

export const FormField = ({
  label,
  id,
  name,
  type = "text",
  value,
  onChange,
  required = true,
  error,
}: FormFieldProps) => {
  const { t } = useTranslation();
  
  return (
    <div className="space-y-2">
      <Label htmlFor={id} className="flex">
        {label}
        {required && <span className="text-destructive ml-1">*</span>}
      </Label>
      <Input
        id={id}
        name={name}
        type={type}
        required={required}
        value={value}
        onChange={onChange}
        className={`w-full ${error ? 'border-destructive' : ''}`}
        aria-invalid={error ? "true" : "false"}
        aria-describedby={error ? `${id}-error` : undefined}
      />
      {error && (
        <p 
          id={`${id}-error`}
          className="text-sm font-medium text-destructive"
          role="alert"
        >
          {t(error)}
        </p>
      )}
    </div>
  );
};
