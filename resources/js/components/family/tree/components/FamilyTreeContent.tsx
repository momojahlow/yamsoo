
import React, { useEffect } from 'react';
import { TreeNode } from "../../types";
import { ScrollArea } from "@/components/ui/scroll-area";
import { FamilyTreeEmpty } from "../components/FamilyTreeEmpty";
import { TreeNodesContainer } from "./TreeNodesContainer";
import { useZoom } from "../hooks/useZoom";
import { ZoomControls } from "./ZoomControls";
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
  
  // Lower initial zoom for better visibility, especially on mobile
  const initialZoom = isMobile ? 0.5 : 0.7;
  
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
      // Use smaller initial zoom to show more of the tree
      setZoomLevel(isMobile ? 0.4 : 0.6);
    }
  }, [layoutComplete, treeNodes.length, setZoomLevel, isMobile]);

  // Show empty state if no data
  if (isEmpty) {
    return <FamilyTreeEmpty />;
  }

  // Use appropriate height and dimensions based on device
  const viewportHeight = isMobile ? '60vh' : '75vh';
  const treeWidth = isMobile ? '1500px' : '2000px';
  const treeHeight = isMobile ? '1500px' : '2000px';

  return (
    <div className="relative border rounded-md overflow-hidden bg-gradient-to-br from-orange-50 to-red-50" style={{ height: viewportHeight }} ref={printAreaRef}>
      <ScrollArea className="h-full w-full">
        <div
          className="relative p-2 sm:p-4 mx-auto"
          style={{
            width: treeWidth,
            height: treeHeight,
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
