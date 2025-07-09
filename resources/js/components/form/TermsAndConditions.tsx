
import { useTranslation } from "react-i18next";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";

interface TermsAndConditionsProps {
  acceptedTerms: {
    conditions: boolean;
    privacy: boolean;
    cookies: boolean;
  };
  onTermsChange: (key: 'conditions' | 'privacy' | 'cookies', value: boolean) => void;
  error?: string;
}

export const TermsAndConditions = ({ 
  acceptedTerms, 
  onTermsChange,
  error 
}: TermsAndConditionsProps) => {
  const { t } = useTranslation();
  
  return (
    <div className="space-y-3">
      <p className="text-sm text-gray-500">
        {t('terms.text')}
      </p>
      
      <div className="flex items-start space-x-2 mt-2">
        <Checkbox 
          id="terms-conditions"
          checked={acceptedTerms.conditions}
          onCheckedChange={(checked) => 
            onTermsChange('conditions', checked === true)
          }
          className="mt-1"
        />
        <Label 
          htmlFor="terms-conditions" 
          className="text-sm font-normal cursor-pointer"
        >
          <a href="/conditions" className="text-primary hover:underline">
            {t('terms.conditions')}
          </a>
        </Label>
      </div>
      
      <div className="flex items-start space-x-2">
        <Checkbox 
          id="terms-privacy"
          checked={acceptedTerms.privacy}
          onCheckedChange={(checked) => 
            onTermsChange('privacy', checked === true)
          }
          className="mt-1"
        />
        <Label 
          htmlFor="terms-privacy" 
          className="text-sm font-normal cursor-pointer"
        >
          <a href="/privacy" className="text-primary hover:underline">
            {t('terms.privacy')}
          </a>
        </Label>
      </div>
      
      <div className="flex items-start space-x-2">
        <Checkbox 
          id="terms-cookies"
          checked={acceptedTerms.cookies}
          onCheckedChange={(checked) => 
            onTermsChange('cookies', checked === true)
          }
          className="mt-1"
        />
        <Label 
          htmlFor="terms-cookies" 
          className="text-sm font-normal cursor-pointer"
        >
          <a href="/cookies" className="text-primary hover:underline">
            {t('terms.cookies')}
          </a>
        </Label>
      </div>
      
      {error && (
        <p className="text-sm font-medium text-destructive mt-2" role="alert">
          {error}
        </p>
      )}
    </div>
  );
};
