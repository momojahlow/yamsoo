
import { useState, useCallback } from "react";

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

export const useSignupValidation = (formData: FormData) => {
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const validateField = useCallback((name: string, value: string): boolean => {
    let error = "";
    
    switch (name) {
      case "firstName":
      case "lastName":
        if (value.length < 2) {
          error = "Doit contenir au moins 2 caractères";
        } else if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(value)) {
          error = "Ne doit contenir que des lettres";
        }
        break;
      
      case "email":
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          error = "Format d'email invalide";
        }
        break;
      
      case "password":
        if (value.length < 8) {
          error = "Le mot de passe doit contenir au moins 8 caractères";
        } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
          error = "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre";
        }
        break;
      
      case "confirmPassword":
        if (value !== formData.password) {
          error = "Les mots de passe ne correspondent pas";
        }
        break;
      
      case "mobile":
        if (value && !/^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/.test(value)) {
          error = "Format de numéro mobile invalide";
        }
        break;
    }

    setFieldErrors(prev => ({
      ...prev,
      [name]: error
    }));

    return error === "";
  }, [formData.password]);

  const validateRequiredFields = useCallback((data: FormData) => {
    const requiredFields = ["firstName", "lastName", "email", "password", "confirmPassword"];
    const hasErrors = requiredFields.some(field => !validateField(field, data[field as keyof FormData]));
    return !hasErrors;
  }, [validateField]);

  return {
    fieldErrors,
    validateField,
    validateRequiredFields,
    setFieldErrors
  };
};
