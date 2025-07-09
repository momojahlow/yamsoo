
import { useState } from "react";

export const useSignupState = () => {
  const [isLoading, setIsLoading] = useState(false);
  
  return {
    isLoading,
    setIsLoading
  };
};
