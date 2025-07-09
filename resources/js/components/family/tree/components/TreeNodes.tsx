
import React from 'react';
import { FamilyMemberCard } from "../FamilyMemberCard";
import { TreeNode } from "../../types";

interface TreeNodesProps {
  nodes: TreeNode[];
  onDeleteRelation: (relationId: string, name: string) => void;
  isDragging?: boolean;
  hasMoved?: boolean;
}

export function TreeNodes({ nodes, onDeleteRelation, isDragging = false, hasMoved = false }: TreeNodesProps) {
  // Add debug logging to help diagnose issues
  if (nodes.length === 0) {
    console.warn("TreeNodes: No nodes provided to render");
  } else {
    console.log(`TreeNodes: Rendering ${nodes.length} nodes`);
    
    // Log one node for debugging
    if (nodes[0]) {
      console.log("First node example:", {
        name: nodes[0].name,
        relation: nodes[0].relation,
        position: { x: nodes[0].x, y: nodes[0].y },
      });
    }
  }
  
  // Sort nodes to ensure consistent rendering order (current user first)
  const sortedNodes = [...nodes].sort((a, b) => {
    // Put "Moi" node first
    if (a.relation === "Moi") return -1;
    if (b.relation === "Moi") return 1;
    
    // Then put spouses
    const aIsSpouse = a.relation === "Mari" || a.relation === "Épouse";
    const bIsSpouse = b.relation === "Mari" || b.relation === "Épouse";
    
    if (aIsSpouse && !bIsSpouse) return -1;
    if (!aIsSpouse && bIsSpouse) return 1;
    
    // Default sort by level and then by x position
    if (a.level !== b.level) {
      return a.level - b.level;
    }
    return (a.x || 0) - (b.x || 0);
  });

  return (
    <div className="relative family-tree-nodes">
      {sortedNodes.map((node) => {
        // Skip nodes without positions
        if (node.x === undefined || node.y === undefined) {
          console.warn(`Node missing position: ${node.name} (${node.relation})`);
          return null;
        }
        
        return (
          <div
            key={node.id}
            className="absolute transition-transform duration-200"
            style={{
              left: `${node.x}px`,
              top: `${node.y}px`,
              transform: 'translate(-50%, -50%)',
              zIndex: node.relation === "Moi" ? 20 : 10 // Higher z-index for current user
            }}
          >
            <FamilyMemberCard
              id={node.id}
              name={node.name}
              avatarUrl={node.avatarUrl}
              relation={node.relation}
              onDelete={() => node.relationId 
                ? onDeleteRelation(node.relationId, node.name) 
                : undefined}
            />
          </div>
        );
      })}
    </div>
  );
}
