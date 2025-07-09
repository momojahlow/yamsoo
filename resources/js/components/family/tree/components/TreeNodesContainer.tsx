
import React, { useEffect, useState, useRef } from 'react';
import { TreeNode } from "../../types";
import { TreeNodes } from "./TreeNodes";
import { useDragNavigation } from "../hooks/useDragNavigation";
import { useTouchNavigation } from "../hooks/useTouchNavigation";
import { useIsMobile } from "@/hooks/use-mobile";
import { getFlattenedUniqueNodes } from "../utils/treeTraversal";
import { FamilyTreeConnections } from "../../FamilyTreeConnections";

interface TreeNodesContainerProps {
  treeNodes: TreeNode[];
  onDeleteRelation: (relationId: string, name: string) => void;
  zoom: number;
}

export function TreeNodesContainer({ 
  treeNodes, 
  onDeleteRelation,
  zoom
}: TreeNodesContainerProps) {
  const isMobile = useIsMobile();
  const [dragStatus, setDragStatus] = useState("ready");
  const scrollViewportRef = useRef<HTMLElement | null>(null);
  const firstRenderRef = useRef(true);
  
  const { 
    containerRef,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    handleMouseLeave,
    isDragging,
    hasMoved
  } = useDragNavigation({
    onDragStart: () => setDragStatus("dragging"),
    onDragEnd: () => setDragStatus("ready")
  });
  
  const {
    handleTouchStart,
    handleTouchMove,
    handleTouchEnd
  } = useTouchNavigation();

  // Get unique flattened nodes
  const uniqueNodes = getFlattenedUniqueNodes(treeNodes);
  
  // Find viewport reference once on mount
  useEffect(() => {
    if (!scrollViewportRef.current) {
      scrollViewportRef.current = document.querySelector('.scroll-area-viewport');
      
      // Make sure our container is visible in dev tools
      if (containerRef.current) {
        containerRef.current.style.border = '1px dotted rgba(0,0,0,0.1)';
      }
    }
  }, []);
  
  // Center the scroll view on the current user node once when loaded
  useEffect(() => {
    if (!containerRef.current || !scrollViewportRef.current || uniqueNodes.length === 0) return;
    
    const centerContent = () => {
      const container = scrollViewportRef.current;
      if (!container) return;
      
      // Find the "Moi" node to center on, fallback to first node if not found
      const currentUser = uniqueNodes.find(node => node.relation === "Moi") || uniqueNodes[0];
      
      if (currentUser && currentUser.x !== undefined && currentUser.y !== undefined) {
        // Log positioning for debugging
        console.log("Centering on node:", currentUser.name, "at", currentUser.x, currentUser.y);
        console.log("Container dimensions:", container.clientWidth, container.clientHeight);
        console.log("Current zoom:", zoom);
        
        // Improved centering calculation with better offset calculation
        const scrollX = (currentUser.x - (container.clientWidth / 2 / zoom));
        const scrollY = (currentUser.y - (container.clientHeight / 2 / zoom));
        
        // Apply scrolling with a small delay for smoother experience
        setTimeout(() => {
          container.scrollTo({
            left: scrollX,
            top: scrollY,
            behavior: firstRenderRef.current ? 'auto' : 'smooth'
          });
          firstRenderRef.current = false;
        }, 200);
      }
    };
    
    // Center content whenever tree nodes change or zoom changes
    centerContent();
  }, [uniqueNodes.length, zoom, treeNodes]); 

  return (
    <div 
      ref={containerRef}
      className={`relative w-full h-full select-none ${isDragging ? 'cursor-grabbing' : 'cursor-grab'}`}
      onMouseDown={handleMouseDown}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      onMouseLeave={handleMouseLeave}
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
      data-print-target="true"
    >
      {/* Draw connection lines between family members */}
      <FamilyTreeConnections rootNodes={treeNodes} />
      
      {/* Render all tree nodes */}
      <TreeNodes 
        nodes={uniqueNodes} 
        onDeleteRelation={onDeleteRelation} 
        isDragging={isDragging} 
        hasMoved={hasMoved}
      />
      
      {/* Show dragging indicator when active */}
      {dragStatus === "dragging" && (
        <div className="absolute bottom-3 left-3 text-xs px-3 py-1 bg-white/90 rounded-full shadow-sm z-50">
          Déplacement en cours...
        </div>
      )}
      
      {/* Debug overlay for development */}
      {false && uniqueNodes.length > 0 && (
        <div className="absolute top-2 left-2 text-xs bg-white/80 p-2 rounded z-50">
          Nodes: {uniqueNodes.length}
        </div>
      )}
    </div>
  );
}
