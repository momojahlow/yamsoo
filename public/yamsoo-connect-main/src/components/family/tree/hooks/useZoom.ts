
import { useState, useCallback, useEffect } from 'react';

// Valeurs limites de zoom
const MIN_ZOOM = 0.4; // Reduced minimum zoom to see more of the tree
const MAX_ZOOM = 2.0;
const ZOOM_STEP = 0.1;

export function useZoom(initialZoom = 1.0) {
  const [zoom, setZoom] = useState(initialZoom);
  
  // Augmenter le niveau de zoom
  const zoomIn = useCallback(() => {
    setZoom(prevZoom => Math.min(MAX_ZOOM, prevZoom + ZOOM_STEP));
  }, []);
  
  // Diminuer le niveau de zoom
  const zoomOut = useCallback(() => {
    setZoom(prevZoom => Math.max(MIN_ZOOM, prevZoom - ZOOM_STEP));
  }, []);
  
  // Réinitialiser le zoom à 100%
  const resetZoom = useCallback(() => {
    setZoom(0.7); // Default to 70% zoom for better initial view
  }, []);
  
  // Définir directement une valeur de zoom
  const setZoomLevel = useCallback((newZoom: number) => {
    const clampedZoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, newZoom));
    setZoom(clampedZoom);
  }, []);
  
  // Support du zoom avec la molette de la souris
  useEffect(() => {
    const handleWheel = (e: WheelEvent) => {
      // Vérifier si Ctrl est enfoncé pour le zoom
      if (e.ctrlKey) {
        e.preventDefault();
        const delta = -Math.sign(e.deltaY) * ZOOM_STEP;
        setZoom(prevZoom => {
          const newZoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, prevZoom + delta));
          return newZoom;
        });
      }
    };
    
    // Cibler le conteneur de l'arbre familial
    const treeContainer = document.querySelector('.scroll-area-viewport');
    if (treeContainer) {
      treeContainer.addEventListener('wheel', handleWheel, { passive: false });
    }
    
    return () => {
      if (treeContainer) {
        treeContainer.removeEventListener('wheel', handleWheel);
      }
    };
  }, []);
  
  return {
    zoom,
    zoomIn,
    zoomOut,
    resetZoom,
    setZoomLevel,
    minZoom: MIN_ZOOM,
    maxZoom: MAX_ZOOM
  };
}
