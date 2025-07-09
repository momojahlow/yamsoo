
import React from "react";
import { useTranslation } from "react-i18next";

export const SignupFormHeader = () => {
  const { t } = useTranslation();

  return (
    <div className="mb-4 text-center">
      <h2 className="text-2xl font-bold text-gray-900">{t('auth.createFreeAccount')}</h2>
      <p className="text-sm text-gray-600 mt-1">
        {t('auth.joinYamsooFree')}
      </p>
    </div>
  );
};
