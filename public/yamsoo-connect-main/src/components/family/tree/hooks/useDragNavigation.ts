import { useState, useRef, useEffect } from 'react';

interface DragNavigationOptions {
  onDragStart?: () => void;
  onDragEnd?: () => void;
}

export function useDragNavigation(options?: DragNavigationOptions) {
  const [isDragging, setIsDragging] = useState(false);
  const [startPoint, setStartPoint] = useState({ x: 0, y: 0 });
  const [scrollPosition, setScrollPosition] = useState({ x: 0, y: 0 });
  const containerRef = useRef<HTMLDivElement>(null);
  const scrollAreaRef = useRef<HTMLElement | null>(null);
  const clickTimerRef = useRef<number | null>(null);
  const [hasMoved, setHasMoved] = useState(false);
  const moveThreshold = 5; // Pixel threshold to determine if we've moved
  
  // Initialize refs on mount and setup window event listener
  useEffect(() => {
    // Initialisation unique au montage du composant
    if (!scrollAreaRef.current) {
      scrollAreaRef.current = document.querySelector('.scroll-area-viewport');
      if (scrollAreaRef.current) {
        setScrollPosition({
          x: scrollAreaRef.current.scrollLeft,
          y: scrollAreaRef.current.scrollTop
        });
      }
    }
    
    // Add window mousemove and mouseup listeners to handle dragging outside the container
    const handleWindowMouseMove = (e: MouseEvent) => {
      if (!isDragging || !startPoint.x || !startPoint.y) return;
      
      // Calculate offset for scrolling
      const offsetX = e.clientX - startPoint.x;
      const offsetY = e.clientY - startPoint.y;
      
      // Update the scroll position of the container based on mouse movement
      if (scrollAreaRef.current) {
        scrollAreaRef.current.scrollLeft = scrollPosition.x - offsetX;
        scrollAreaRef.current.scrollTop = scrollPosition.y - offsetY;
      }
    };

    const handleWindowMouseUp = () => {
      if (isDragging) {
        setIsDragging(false);
        document.body.style.cursor = 'auto';
        if (containerRef.current) {
          containerRef.current.style.cursor = 'grab';
        }
        
        if (options?.onDragEnd) options.onDragEnd();
        
        // Reset hasMoved state
        setTimeout(() => {
          setHasMoved(false);
        }, 50);
      }
      
      if (clickTimerRef.current !== null) {
        clearTimeout(clickTimerRef.current);
        clickTimerRef.current = null;
      }
    };
    
    window.addEventListener('mousemove', handleWindowMouseMove);
    window.addEventListener('mouseup', handleWindowMouseUp);
    
    // Cleanup on unmount
    return () => {
      window.removeEventListener('mousemove', handleWindowMouseMove);
      window.removeEventListener('mouseup', handleWindowMouseUp);
      
      if (clickTimerRef.current !== null) {
        clearTimeout(clickTimerRef.current);
      }
    };
  }, [isDragging, startPoint, scrollPosition, options]);

  // Mouse event handlers
  const handleMouseDown = (e: React.MouseEvent) => {
    if (e.button !== 0) return; // Only respond to left mouse button
    
    e.preventDefault(); // Prevent text selection during drag
    
    setHasMoved(false);
    setStartPoint({ x: e.clientX, y: e.clientY });
    
    // Store the current scroll position
    if (scrollAreaRef.current) {
      setScrollPosition({
        x: scrollAreaRef.current.scrollLeft,
        y: scrollAreaRef.current.scrollTop
      });
    }
    
    // Set dragging immediately for better responsiveness
    setIsDragging(true);
    
    // Change cursor style
    document.body.style.cursor = 'grabbing';
    if (containerRef.current) {
      containerRef.current.style.cursor = 'grabbing';
    }
    
    if (options?.onDragStart) options.onDragStart();
  };

  const handleMouseMove = (e: React.MouseEvent) => {
    if (!isDragging || !startPoint.x || !startPoint.y) return;
    
    e.preventDefault();
    
    // Calculate distance moved
    const dx = Math.abs(e.clientX - startPoint.x);
    const dy = Math.abs(e.clientY - startPoint.y);
    
    // Set hasMoved if we moved significantly
    if (dx > moveThreshold || dy > moveThreshold) {
      setHasMoved(true);
    }
    
    // Calculate offset for scrolling
    const offsetX = e.clientX - startPoint.x;
    const offsetY = e.clientY - startPoint.y;
    
    // Update the scroll position of the container based on mouse movement
    if (scrollAreaRef.current) {
      scrollAreaRef.current.scrollLeft = scrollPosition.x - offsetX;
      scrollAreaRef.current.scrollTop = scrollPosition.y - offsetY;
    }
  };

  const handleMouseUp = () => {
    if (isDragging) {
      setIsDragging(false);
      
      // Restore cursor style
      document.body.style.cursor = 'auto';
      if (containerRef.current) {
        containerRef.current.style.cursor = 'grab';
      }
      
      if (options?.onDragEnd) options.onDragEnd();
      
      // Reset hasMoved after a short delay to allow click events to complete
      setTimeout(() => {
        setHasMoved(false);
      }, 50);
    }
  };

  const handleMouseLeave = () => {
    // We now keep dragging even when mouse leaves container
    // The window event listeners handle the rest
  };

  return {
    isDragging,
    hasMoved,
    containerRef,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    handleMouseLeave
  };
}
