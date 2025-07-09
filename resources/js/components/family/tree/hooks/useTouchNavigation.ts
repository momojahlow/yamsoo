
import { useState, useRef, useEffect } from 'react';

export function useTouchNavigation() {
  // Track touch points for mobile
  const [touchPoints, setTouchPoints] = useState<{ identifier: number; x: number; y: number }[]>([]);
  const [scrollPosition, setScrollPosition] = useState({ x: 0, y: 0 });
  const scrollAreaRef = useRef<HTMLElement | null>(null);
  const [isDragging, setIsDragging] = useState(false);

  // Initialize reference to scroll area on mount
  useEffect(() => {
    // Wait for the DOM to be ready before finding the scroll area
    setTimeout(() => {
      scrollAreaRef.current = document.querySelector('.scroll-area-viewport');
    }, 100);
    
    // Cleanup function
    return () => {
      setIsDragging(false);
    };
  }, []);

  // Touch event handlers for mobile
  const handleTouchStart = (e: React.TouchEvent) => {
    e.stopPropagation();
    
    // Store information for each touch point
    const newTouchPoints = Array.from(e.touches).map(touch => ({
      identifier: touch.identifier,
      x: touch.clientX,
      y: touch.clientY
    }));
    
    setTouchPoints(newTouchPoints);
    setIsDragging(true);
    
    // Store the current scroll position
    if (scrollAreaRef.current) {
      setScrollPosition({
        x: scrollAreaRef.current.scrollLeft,
        y: scrollAreaRef.current.scrollTop
      });
    }
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    e.preventDefault(); // Prevent default scrolling
    e.stopPropagation();
    
    // If only one finger is used, move the view
    if (e.touches.length === 1 && touchPoints.length === 1) {
      const touch = e.touches[0];
      const startTouch = touchPoints.find(t => t.identifier === touch.identifier);
      
      if (startTouch && scrollAreaRef.current) {
        const dx = touch.clientX - startTouch.x;
        const dy = touch.clientY - startTouch.y;
        
        // Update scroll position with smoother movement
        scrollAreaRef.current.scrollLeft = scrollPosition.x - dx;
        scrollAreaRef.current.scrollTop = scrollPosition.y - dy;
      }
    }
  };

  const handleTouchEnd = (e: React.TouchEvent) => {
    e.stopPropagation();
    
    // Reset touch points
    setTouchPoints([]);
    setIsDragging(false);
    
    // Store current scroll position
    if (scrollAreaRef.current) {
      setScrollPosition({
        x: scrollAreaRef.current.scrollLeft,
        y: scrollAreaRef.current.scrollTop
      });
    }
  };

  return {
    handleTouchStart,
    handleTouchMove,
    handleTouchEnd,
    isDragging
  };
}
