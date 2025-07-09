
import * as React from "react";

// Updated to match the common mobile breakpoint
const MOBILE_BREAKPOINT = 768; // Changed from 640 to 768 for a better tablet/mobile experience

export function useIsMobile() {
  const [isMobile, setIsMobile] = React.useState<boolean>(false);
  const [isMobileDevice, setIsMobileDevice] = React.useState<boolean>(false);

  React.useEffect(() => {
    // Initial check for screen size
    const checkScreenSize = () => {
      setIsMobile(window.innerWidth < MOBILE_BREAKPOINT);
    };
    
    // Initial check for mobile device based on user agent
    const checkDevice = () => {
      const userAgent = navigator.userAgent || navigator.vendor || (window as any).opera;
      const mobileRegex = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
      setIsMobileDevice(mobileRegex.test(userAgent));
    };
    
    // Run initial checks
    checkScreenSize();
    checkDevice();
    
    // Setup event listener for resize
    const handleResize = () => {
      checkScreenSize();
    };
    
    window.addEventListener('resize', handleResize);
    
    // Cleanup
    return () => {
      window.removeEventListener('resize', handleResize);
    };
  }, []);

  return isMobile || isMobileDevice;
}
