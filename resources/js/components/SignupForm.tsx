
import { PersonalInfoFields } from "@/components/form/PersonalInfoFields";
import { ContactInfoFields } from "@/components/form/ContactInfoFields";
import { PasswordFields } from "@/components/form/PasswordFields";
import { OptionalFields } from "@/components/form/OptionalFields";
import { TermsAndConditions } from "@/components/form/TermsAndConditions";
import { SignupFormHeader } from "@/components/form/SignupFormHeader";
import { SignupFormActions } from "@/components/form/SignupFormActions";
import { useSignupForm } from "@/hooks/useSignupForm";
import { useState } from "react";

const SignupForm = () => {
  const {
    formData,
    fieldErrors,
    isLoading,
    handleChange,
    handleSubmit,
    setFormData
  } = useSignupForm();

  const [acceptedTerms, setAcceptedTerms] = useState({
    conditions: false,
    privacy: false,
    cookies: false
  });

  const [termsError, setTermsError] = useState("");

  const handleTermsChange = (key: 'conditions' | 'privacy' | 'cookies', value: boolean) => {
    setAcceptedTerms(prev => ({
      ...prev,
      [key]: value
    }));
    
    // Clear error if at least one is checked
    if (value) {
      setTermsError("");
    }
  };

  const validateTerms = (): boolean => {
    const isValid = acceptedTerms.conditions && acceptedTerms.privacy && acceptedTerms.cookies;
    
    if (!isValid) {
      setTermsError("Vous devez accepter les conditions pour créer un compte");
    }
    
    return isValid;
  };

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validate terms before submitting
    const termsValid = validateTerms();
    if (!termsValid) {
      return;
    }
    
    handleSubmit(e);
  };

  const hasErrors = Object.values(fieldErrors).some(error => error !== "") || termsError !== "";

  return (
    <form onSubmit={onSubmit} className="space-y-6 w-full max-w-md mx-auto p-6 bg-white rounded-lg shadow-lg animate-fadeIn">
      <SignupFormHeader />

      <PersonalInfoFields
        firstName={formData.firstName}
        lastName={formData.lastName}
        onChange={handleChange}
        errors={fieldErrors}
      />

      <ContactInfoFields
        email={formData.email}
        mobile={formData.mobile}
        onChange={handleChange}
        errors={fieldErrors}
      />

      <PasswordFields
        password={formData.password}
        confirmPassword={formData.confirmPassword}
        onChange={handleChange}
        errors={fieldErrors}
      />

      <OptionalFields
        birthDate={formData.birthDate}
        gender={formData.gender}
        onChange={handleChange}
        onGenderChange={(value) => setFormData((prev) => ({ ...prev, gender: value }))}
        errors={fieldErrors}
      />

      <TermsAndConditions 
        acceptedTerms={acceptedTerms}
        onTermsChange={handleTermsChange}
        error={termsError}
      />

      <SignupFormActions isLoading={isLoading} hasErrors={hasErrors} />
    </form>
  );
};

export default SignupForm;
