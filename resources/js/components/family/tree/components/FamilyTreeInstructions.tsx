
import { Tablet, MousePointer, Move, ZoomIn } from "lucide-react";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { useIsMobile } from "@/hooks/use-mobile";
import { useTranslation } from "react-i18next";

export function FamilyTreeInstructions() {
  const isMobile = useIsMobile();
  const { t } = useTranslation();
  
  return (
    <Alert className="bg-blue-50 border-blue-200">
      <AlertDescription className="flex flex-col gap-2">
        <div className="flex items-center gap-2 text-blue-700">
          {isMobile ? (
            <>
              <Tablet className="h-4 w-4" />
              <span className="text-sm">Faites glisser pour naviguer dans l'arbre</span>
            </>
          ) : (
            <>
              <div className="flex items-center gap-2">
                <MousePointer className="h-4 w-4" />
                <Move className="h-4 w-4" />
                <span className="text-sm">{t('family.treeNavigation')}</span>
              </div>
              
              <div className="flex items-center gap-2 mt-1">
                <ZoomIn className="h-4 w-4" />
                <span className="text-sm">{t('family.zoomControls')}</span>
              </div>
            </>
          )}
        </div>
        
        {isMobile && (
          <div className="text-sm text-blue-700 italic">
            {t('family.horizontalScroll')}
          </div>
        )}
      </AlertDescription>
    </Alert>
  );
}
