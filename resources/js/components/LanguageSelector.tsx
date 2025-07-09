
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useTranslation } from "react-i18next";
import { Globe } from "lucide-react";

export const LanguageSelector = () => {
  const { i18n, t } = useTranslation();

  const handleLanguageChange = (value: string) => {
    i18n.changeLanguage(value);
    // Update document direction for Arabic
    document.documentElement.dir = value === 'ar' ? 'rtl' : 'ltr';
  };

  const getLanguageLabel = (code: string) => {
    switch (code) {
      case 'fr':
        return 'Français';
      case 'ar':
        return 'العربية';
      default:
        return code;
    }
  };

  return (
    <div className="flex items-center gap-2">
      <Globe className="h-4 w-4 text-gray-500" />
      <Select value={i18n.language} onValueChange={handleLanguageChange}>
        <SelectTrigger className="w-[140px] bg-white">
          <SelectValue aria-label={t('language.select')}>
            {getLanguageLabel(i18n.language)}
          </SelectValue>
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="fr" className="cursor-pointer">
            Français
          </SelectItem>
          <SelectItem value="ar" className="cursor-pointer font-arabic">
            العربية
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
  );
};
