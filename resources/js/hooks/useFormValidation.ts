interface ValidationErrors {
  firstName?: string;
  lastName?: string;
  email?: string;
  password?: string;
  confirmPassword?: string;
}

interface FormData {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
  confirmPassword: string;
  mobile?: string;
  birthDate?: string;
  gender?: string;
}

export const useFormValidation = () => {
  const validateForm = (formData: FormData): string[] => {
    const errors: string[] = [];
    
    if (!formData.firstName) errors.push("Le prénom est requis");
    if (!formData.lastName) errors.push("Le nom est requis");
    if (!formData.email) errors.push("L'email est requis");
    if (!formData.password) errors.push("Le mot de passe est requis");
    
    if (formData.password !== formData.confirmPassword) {
      errors.push("Les mots de passe ne correspondent pas");
    }

    return errors;
  };

  return { validateForm };
};