
import React, { useState, useEffect, useRef } from 'react';
import { TreeNode } from "./types";
import { FamilyMemberCard } from "./FamilyMemberCard";

interface TreeNodesDisplayProps {
  treeNodes: TreeNode[];
  layoutComplete: boolean;
  onDeleteRelation: (relationId: string, name: string) => void;
}

function traverseTree(node: TreeNode): TreeNode[] {
  const result: TreeNode[] = [node];
  for (const child of node.children) {
    result.push(...traverseTree(child));
  }
  return result;
}

export function TreeNodesDisplay({ 
  treeNodes, 
  layoutComplete, 
  onDeleteRelation 
}: TreeNodesDisplayProps) {
  const [isDragging, setIsDragging] = useState(false);
  const [startPoint, setStartPoint] = useState({ x: 0, y: 0 });
  const [scrollPosition, setScrollPosition] = useState({ x: 0, y: 0 });
  const containerRef = useRef<HTMLDivElement>(null);

  // Initialize the scroll position
  useEffect(() => {
    const container = document.querySelector('.scroll-area-viewport') as HTMLElement;
    if (container) {
      setScrollPosition({
        x: container.scrollLeft,
        y: container.scrollTop
      });
    }
  }, [layoutComplete]);

  if (!layoutComplete || treeNodes.length === 0) {
    return null;
  }

  // Get all nodes in a flat array to render them
  const allNodes = treeNodes.flatMap(traverseTree);

  // Remove duplicate nodes based on ID
  const uniqueNodes = Array.from(
    new Map(allNodes.map(node => [node.id, node])).values()
  );

  // Log spouse relationships for debugging
  const spouses = uniqueNodes.filter(node => node.relation === "Mari" || node.relation === "Épouse");
  const currentUser = uniqueNodes.find(node => node.relation === "Moi");
  
  if (spouses.length > 0 && currentUser) {
    console.log("Current user:", currentUser.name, "at position:", currentUser.x, currentUser.y);
    spouses.forEach(spouse => {
      console.log(`Spouse ${spouse.name} positioned at: ${spouse.x}, ${spouse.y}`);
    });
  }

  const handleMouseDown = (e: React.MouseEvent) => {
    e.preventDefault(); // Prevent text selection during drag
    setIsDragging(true);
    setStartPoint({ x: e.clientX, y: e.clientY });
    
    // Store the current scroll position
    const container = document.querySelector('.scroll-area-viewport') as HTMLElement;
    if (container) {
      setScrollPosition({
        x: container.scrollLeft,
        y: container.scrollTop
      });
    }
    
    // Change cursor style
    document.body.style.cursor = 'grabbing';
    if (containerRef.current) {
      containerRef.current.style.cursor = 'grabbing';
    }
  };

  const handleMouseMove = (e: React.MouseEvent) => {
    if (!isDragging) return;
    
    const dx = e.clientX - startPoint.x;
    const dy = e.clientY - startPoint.y;
    
    // Update the scroll position of the container based on mouse movement
    const container = document.querySelector('.scroll-area-viewport') as HTMLElement;
    if (container) {
      container.scrollLeft = scrollPosition.x - dx;
      container.scrollTop = scrollPosition.y - dy;
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
      
      // Store current scroll position
      const container = document.querySelector('.scroll-area-viewport') as HTMLElement;
      if (container) {
        setScrollPosition({
          x: container.scrollLeft,
          y: container.scrollTop
        });
      }
    }
  };

  // Enable mouse leave to catch if the mouse leaves the window while dragging
  const handleMouseLeave = () => {
    if (isDragging) {
      setIsDragging(false);
      document.body.style.cursor = 'auto';
      if (containerRef.current) {
        containerRef.current.style.cursor = 'grab';
      }
    }
  };

  return (
    <div 
      ref={containerRef}
      className="relative w-full h-full cursor-grab" 
      onMouseDown={handleMouseDown}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      onMouseLeave={handleMouseLeave}
      data-print-target="true"
    >
      <div className="relative">
        {uniqueNodes.map((node) => (
          <FamilyMemberCard
            key={node.id}
            id={node.id}
            name={node.name}
            avatarUrl={node.avatarUrl}
            relation={node.relation}
            onDelete={() => node.relationId 
              ? onDeleteRelation(node.relationId, node.name) 
              : null}
            style={{
              left: `${node.x}px`,
              top: `${node.y}px`,
              position: 'absolute',
              transform: 'translate(-50%, -50%)',
              zIndex: 10,
              width: '150px', // Légèrement réduit pour mieux s'adapter
              boxShadow: '0 4px 8px -1px rgba(0, 0, 0, 0.15), 0 2px 6px -1px rgba(0, 0, 0, 0.1)'
            }}
          />
        ))}
      </div>
    </div>
  );
}
