
export interface SignupFormData {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
  mobile?: string;
  birthDate?: string;
  gender?: string;
}

export const cleanSignupData = (formData: SignupFormData) => {
  const cleanedData = {
    firstName: formData.firstName.trim(),
    lastName: formData.lastName.trim(),
    email: formData.email.trim().toLowerCase(),
    mobile: formData.mobile?.trim(),
    birthDate: formData.birthDate,
    gender: formData.gender
  };

  return cleanedData;
};

export const validateRequiredFields = (cleanedData: ReturnType<typeof cleanSignupData>) => {
  if (!cleanedData.firstName || !cleanedData.lastName || !cleanedData.email) {
    console.error("❌ Champs requis manquants");
    throw new Error("Champs requis manquants");
  }
};
