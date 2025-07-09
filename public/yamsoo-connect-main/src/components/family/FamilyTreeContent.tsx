
import React, { useEffect } from 'react';
import { TreeNode } from "./tree/types";
import { ScrollArea } from "@/components/ui/scroll-area";
import { FamilyTreeEmpty } from "./tree/components/FamilyTreeEmpty";
import { TreeNodesContainer } from "./tree/components/TreeNodesContainer";
import { useZoom } from "./tree/hooks/useZoom";
import { ZoomControls } from "./tree/components/ZoomControls";
import { useIsMobile } from "@/hooks/use-mobile";

interface FamilyTreeContentProps {
  treeNodes: TreeNode[];
  layoutComplete: boolean;
  isEmpty: boolean;
  calculateMaxLevel: () => number;
  onDeleteRelation: (relationId: string, name: string) => void;
  printAreaRef: React.RefObject<HTMLDivElement>;
}

export function FamilyTreeContent({
  treeNodes,
  layoutComplete,
  isEmpty,
  calculateMaxLevel,
  onDeleteRelation,
  printAreaRef
}: FamilyTreeContentProps) {
  const isMobile = useIsMobile();
  
  // Use appropriate initial zoom based on device
  const initialZoom = isMobile ? 0.7 : 0.85;
  
  const {
    zoom,
    zoomIn,
    zoomOut,
    resetZoom,
    setZoomLevel,
    minZoom,
    maxZoom
  } = useZoom(initialZoom);

  // Reset zoom when tree changes
  useEffect(() => {
    if (layoutComplete && treeNodes.length > 0) {
      // Use smaller zoom for initial view to show more of the tree
      setZoomLevel(isMobile ? 0.6 : 0.7); 
    }
  }, [layoutComplete, treeNodes.length, setZoomLevel, isMobile]);

  // Show empty state if no data
  if (isEmpty) {
    return <FamilyTreeEmpty />;
  }

  // Use appropriate height based on device
  const viewportHeight = isMobile ? '65vh' : '75vh';
  
  return (
    <div className="relative border rounded-md overflow-hidden" style={{ height: viewportHeight }} ref={printAreaRef}>
      <ScrollArea className="h-full w-full">
        <div 
          className="relative p-4 mx-auto"
          style={{ 
            width: '1600px', // Increased width to accommodate larger trees
            height: '1200px', // Increased height to accommodate more generations
            transformOrigin: "center center",
            transform: `scale(${zoom})`,
            transition: "transform 0.2s ease-out"
          }}
        >
          {layoutComplete && treeNodes.length > 0 && (
            <TreeNodesContainer 
              treeNodes={treeNodes} 
              onDeleteRelation={onDeleteRelation} 
              zoom={zoom}
            />
          )}
        </div>
      </ScrollArea>
      
      <ZoomControls
        zoom={zoom}
        minZoom={minZoom}
        maxZoom={maxZoom}
        onZoomIn={zoomIn}
        onZoomOut={zoomOut}
        onReset={resetZoom}
        onZoomChange={setZoomLevel}
      />
    </div>
  );
}
