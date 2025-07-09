
import { YamsooLogo } from "@/components/logo/YamsooLogo";
import { useTranslation } from "react-i18next";

export const AuthHeader = () => {
  const { t } = useTranslation();
  
  return (
    <div className="text-center space-y-2">
      <div className="flex justify-center mb-6">
        <YamsooLogo size={90} />
      </div>
      <h1 className="text-2xl font-bold text-terracotta">
        {t('auth.connexion')}
      </h1>
      <p className="text-sm text-gray-600">
        {t('auth.subtitle')}
      </p>
    </div>
  );
};
