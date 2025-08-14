import { usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface TranslationData {
  [key: string]: string | TranslationData;
}

interface PageProps {
  translations?: TranslationData;
  locale?: string;
  available_locales?: { [key: string]: string };
}

export function useTranslation() {
  const { props } = usePage<PageProps>();
  const [translations, setTranslations] = useState<TranslationData>(props.translations || {});
  const [currentLocale, setCurrentLocale] = useState<string>(props.locale || 'fr');
  const [availableLocales, setAvailableLocales] = useState<{ [key: string]: string }>(
    props.available_locales || { fr: 'Français', ar: 'العربية' }
  );

  // Fonction pour obtenir une traduction
  const t = (key: string, replacements: { [key: string]: string | number } = {}): string => {
    const keys = key.split('.');
    let value: any = translations;

    // Naviguer dans l'objet de traductions
    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k];
      } else {
        // Si la clé n'existe pas, retourner la clé elle-même
        return key;
      }
    }

    // Si la valeur finale n'est pas une chaîne, retourner la clé
    if (typeof value !== 'string') {
      return key;
    }

    // Remplacer les placeholders
    let result = value;
    Object.entries(replacements).forEach(([placeholder, replacement]) => {
      result = result.replace(new RegExp(`:${placeholder}`, 'g'), String(replacement));
    });

    return result;
  };

  // Fonction pour changer de langue
  const switchLanguage = async (locale: string) => {
    try {
      // Faire une requête pour changer la langue
      window.location.href = `/language/${locale}`;
    } catch (error) {
      console.error('Erreur lors du changement de langue:', error);
    }
  };

  // Fonction pour obtenir la direction du texte (LTR/RTL)
  const getTextDirection = (): 'ltr' | 'rtl' => {
    return currentLocale === 'ar' ? 'rtl' : 'ltr';
  };

  // Fonction pour obtenir la classe CSS pour la direction
  const getDirectionClass = (): string => {
    return currentLocale === 'ar' ? 'rtl' : 'ltr';
  };

  // Fonction pour obtenir le nom de la langue actuelle
  const getCurrentLanguageName = (): string => {
    return availableLocales[currentLocale] || currentLocale;
  };

  // Fonction pour obtenir la langue opposée (pour le toggle)
  const getOppositeLanguage = (): { code: string; name: string } => {
    const opposite = currentLocale === 'fr' ? 'ar' : 'fr';
    return {
      code: opposite,
      name: availableLocales[opposite] || opposite
    };
  };

  // Mettre à jour les traductions quand les props changent
  useEffect(() => {
    if (props.translations) {
      setTranslations(props.translations);
    }
    if (props.locale) {
      setCurrentLocale(props.locale);
    }
    if (props.available_locales) {
      setAvailableLocales(props.available_locales);
    }
  }, [props.translations, props.locale, props.available_locales]);

  // Appliquer la direction du texte au document
  useEffect(() => {
    document.documentElement.dir = getTextDirection();
    document.documentElement.lang = currentLocale;
  }, [currentLocale]);

  return {
    t,
    currentLocale,
    availableLocales,
    switchLanguage,
    getTextDirection,
    getDirectionClass,
    getCurrentLanguageName,
    getOppositeLanguage,
    isRTL: currentLocale === 'ar',
    isLTR: currentLocale !== 'ar'
  };
}
