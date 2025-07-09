
import React from "react";

interface YamsooLogoProps {
  size?: number;
  className?: string;
}

export const YamsooLogo = ({ size = 48, className = "" }: YamsooLogoProps) => {
  return (
    <div 
      className={`font-bold text-primary flex items-center justify-center ${className}`}
      style={{ 
        fontSize: size * 0.5,
        height: size,
        minWidth: size * 2,
      }}
    >
      Yamsoo!
    </div>
  );
};
