
import { useState, useCallback } from "react";
import { useSignup } from "@/hooks/useSignup";
import { useToast } from "@/hooks/use-toast";
import { useSignupValidation } from "@/hooks/useSignupValidation";
import { useSignupSubmit } from "@/hooks/useSignupSubmit";
import { sanitizeTextInput, sanitizeEmailInput } from "@/utils/inputSanitization";

interface FormData {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
  confirmPassword: string;
  mobile: string;
  birthDate: string;
  gender: string;
}

export const useSignupForm = () => {
  const { isLoading } = useSignup();
  const { toast } = useToast();

  const [formData, setFormData] = useState<FormData>({
    firstName: "",
    lastName: "",
    email: "",
    password: "",
    confirmPassword: "",
    mobile: "",
    birthDate: "",
    gender: "",
  });

  const { fieldErrors, validateField, validateRequiredFields } = useSignupValidation(formData);
  const { submitSignup } = useSignupSubmit();

  const handleChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    
    // SECURITY IMPROVEMENT: Sanitize input based on field type
    let sanitizedValue = value;
    if (name === 'email') {
      sanitizedValue = sanitizeEmailInput(value);
    } else if (['firstName', 'lastName', 'mobile'].includes(name)) {
      sanitizedValue = sanitizeTextInput(value);
    }
    
    console.log(`Field '${name}' updated`);
    
    setFormData(prev => ({
      ...prev,
      [name]: sanitizedValue,
    }));

    validateField(name, sanitizedValue);
  }, [validateField]);

  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateRequiredFields(formData)) {
      console.log("❌ Form validation failed");
      toast({
        variant: "destructive",
        title: "Erreur de validation",
        description: "Veuillez corriger les erreurs dans le formulaire",
      });
      return;
    }

    await submitSignup(formData);
  }, [formData, fieldErrors, validateRequiredFields, toast, submitSignup]);

  return {
    formData,
    fieldErrors,
    isLoading,
    handleChange,
    handleSubmit,
    setFormData
  };
};
