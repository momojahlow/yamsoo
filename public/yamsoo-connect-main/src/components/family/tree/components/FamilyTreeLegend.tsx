
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Printer, Info } from "lucide-react";
import { TreeNode } from "../../types";
import { useIsMobile } from "@/hooks/use-mobile";
import { useTranslation } from "react-i18next";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

interface FamilyTreeLegendProps {
  isEmpty: boolean;
  layoutComplete: boolean;
  treeNodes: TreeNode[];
  onPrintClick: () => void;
}

export function FamilyTreeLegend({ 
  isEmpty, 
  layoutComplete, 
  treeNodes, 
  onPrintClick 
}: FamilyTreeLegendProps) {
  const isMobile = useIsMobile();
  const { t } = useTranslation();
  
  // Only show print button if we have nodes to print
  const showPrintButton = layoutComplete && treeNodes.length > 0 && !isEmpty;
  
  const relationTypes = [
    { name: 'Moi', color: 'bg-yellow-100 border-yellow-300' },
    { name: 'Parents', color: 'bg-blue-100 border-blue-300' },
    { name: 'Grands-parents', color: 'bg-purple-100 border-purple-300' },
    { name: 'Frères/Sœurs', color: 'bg-green-100 border-green-300' },
    { name: 'Enfants', color: 'bg-pink-100 border-pink-300' },
    { name: 'Petits-enfants', color: 'bg-orange-100 border-orange-300' },
    { name: 'Conjoints', color: 'bg-rose-100 border-rose-300' },
  ];

  return (
    <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
      {!isMobile && (
        <div className="flex flex-wrap gap-2 items-center">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex items-center gap-1 text-muted-foreground">
                  <Info className="h-4 w-4" />
                  <span className="text-sm">{t('family.legend')}</span>
                </div>
              </TooltipTrigger>
              <TooltipContent>
                <div className="grid grid-cols-2 gap-2 p-1">
                  {relationTypes.map((relation) => (
                    <div key={relation.name} className="flex items-center gap-2">
                      <div className={`w-3 h-3 rounded-full ${relation.color}`}></div>
                      <span className="text-xs">{relation.name}</span>
                    </div>
                  ))}
                </div>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )}
      
      {showPrintButton && (
        <Button 
          variant="outline" 
          size={isMobile ? "sm" : "default"} 
          className="ml-auto"
          onClick={onPrintClick}
          data-testid="family.printTree"
        >
          <Printer className={`${isMobile ? 'h-3.5 w-3.5' : 'h-4 w-4'} mr-2`} />
          {t('family.printTree')}
        </Button>
      )}
    </div>
  );
}
