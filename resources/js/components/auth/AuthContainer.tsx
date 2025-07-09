
import React from "react";
import { useIsMobile } from "@/hooks/use-mobile";

interface AuthContainerProps {
  children: React.ReactNode;
}

export const AuthContainer = ({ children }: AuthContainerProps) => {
  const isMobile = useIsMobile();
  
  return (
    <div className="min-h-screen bg-gradient-to-b from-sand to-sand-dark flex items-center justify-center p-4 safe-area">
      <div className={`${isMobile ? 'bg-transparent' : 'bg-white'} ${isMobile ? 'p-5' : 'p-8'} ${!isMobile && 'rounded-lg shadow-lg'} w-full max-w-md mx-auto space-y-5 safe-bottom`}>
        {children}
      </div>
    </div>
  );
}
