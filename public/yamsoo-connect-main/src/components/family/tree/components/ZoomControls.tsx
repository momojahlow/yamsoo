
import React from 'react';
import { ZoomIn, ZoomOut, RefreshCw } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Slider } from '@/components/ui/slider';
import { useIsMobile } from '@/hooks/use-mobile';
import { useTranslation } from 'react-i18next';

interface ZoomControlsProps {
  zoom: number;
  minZoom: number;
  maxZoom: number;
  onZoomIn: () => void;
  onZoomOut: () => void;
  onReset: () => void;
  onZoomChange: (value: number) => void;
}

export function ZoomControls({
  zoom,
  minZoom,
  maxZoom,
  onZoomIn,
  onZoomOut,
  onReset,
  onZoomChange
}: ZoomControlsProps) {
  const isMobile = useIsMobile();
  const { t } = useTranslation();
  
  const handleSliderChange = (value: number[]) => {
    onZoomChange(value[0]);
  };
  
  // Convert zoom value to percentage for display
  const zoomPercentage = Math.round(zoom * 100);
  
  return (
    <div 
      data-testid="family.zoomControls" 
      className="flex flex-col items-center gap-2 bg-white/80 backdrop-blur-sm p-2 rounded-lg shadow-md absolute top-4 right-4 z-20"
      style={{ touchAction: 'none' }} // Prevents touch events from scrolling the page
    >
      <Button
        variant="outline"
        size="icon"
        onClick={onZoomIn}
        title={t('family.zoomIn')}
        className="h-8 w-8 rounded-full bg-white shadow-sm"
      >
        <ZoomIn className="h-4 w-4" />
      </Button>
      
      <div className="flex flex-col items-center gap-2">
        <Slider
          value={[zoom]}
          min={minZoom}
          max={maxZoom}
          step={0.1}
          onValueChange={handleSliderChange}
          orientation="vertical"
          className={isMobile ? "h-24" : "h-32"}
          dir="rtl" // For values to increase upwards
        />
        <span className="text-xs font-medium w-12 text-center bg-white rounded-md py-1 shadow-sm">
          {zoomPercentage}%
        </span>
      </div>
      
      <Button
        variant="outline"
        size="icon"
        onClick={onZoomOut}
        title={t('family.zoomOut')}
        className="h-8 w-8 rounded-full bg-white shadow-sm"
      >
        <ZoomOut className="h-4 w-4" />
      </Button>
      
      <Button
        variant="ghost"
        size="icon"
        onClick={onReset}
        title={t('family.resetZoom')}
        className="h-8 w-8 rounded-full bg-white shadow-sm mt-1"
      >
        <RefreshCw className="h-4 w-4" />
      </Button>
    </div>
  );
}
