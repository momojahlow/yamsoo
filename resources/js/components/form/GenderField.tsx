
import { Label } from "@/components/ui/label";
import { useTranslation } from "react-i18next";
import { Checkbox } from "@/components/ui/checkbox";
import { useState, useEffect } from "react";

interface GenderFieldProps {
  value: string;
  onChange: (value: string) => void;
  error?: string;
}

export const GenderField = ({ value, onChange, error }: GenderFieldProps) => {
  const { t } = useTranslation();
  const [selectedGender, setSelectedGender] = useState<string>(value);

  // Handle gender selection change
  const handleGenderChange = (gender: string) => {
    // If the same gender is clicked again, unselect it
    const newValue = selectedGender === gender ? "" : gender;
    setSelectedGender(newValue);
    onChange(newValue);
  };

  useEffect(() => {
    setSelectedGender(value);
  }, [value]);

  return (
    <div className="space-y-4">
      <Label className={error ? "text-destructive" : ""}>
        {t("gender.label")}
      </Label>
      
      <div className="flex flex-row gap-6 items-center">
        <div className="flex items-center space-x-2">
          <Checkbox
            id="gender-male"
            checked={selectedGender === "male"}
            onCheckedChange={() => handleGenderChange("male")}
          />
          <Label
            htmlFor="gender-male"
            className="text-sm font-normal cursor-pointer"
          >
            Homme
          </Label>
        </div>

        <div className="flex items-center space-x-2">
          <Checkbox
            id="gender-female"
            checked={selectedGender === "female"}
            onCheckedChange={() => handleGenderChange("female")}
          />
          <Label
            htmlFor="gender-female"
            className="text-sm font-normal cursor-pointer"
          >
            Femme
          </Label>
        </div>
      </div>
      
      {error && (
        <p className="text-sm font-medium text-destructive" role="alert">
          {error}
        </p>
      )}
    </div>
  );
};
