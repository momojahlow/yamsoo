
import { useState, useEffect } from "react";
import { FamilyRelation } from "@/types/family";
import { TreeNode } from "../types";
import { buildFamilyTree } from "../../utils/treeBuilder";
import { useIsMobile } from "@/hooks/use-mobile";

/**
 * Hook for calculating family tree layout
 */
export function useTreeLayout(relations: FamilyRelation[]) {
  const [treeNodes, setTreeNodes] = useState<TreeNode[]>([]);
  const [layoutComplete, setLayoutComplete] = useState(false);
  const isMobile = useIsMobile();
  
  // Calculate maximum level in the tree
  const calculateMaxLevel = () => {
    let maxLevel = 0;
    
    const traverseForMaxLevel = (node: TreeNode) => {
      maxLevel = Math.max(maxLevel, node.level);
      for (const child of node.children) {
        traverseForMaxLevel(child);
      }
    };
    
    treeNodes.forEach(traverseForMaxLevel);
    return maxLevel;
  };
  
  // Process family relations and build tree
  useEffect(() => {
    if (relations.length === 0) {
      setTreeNodes([]);
      setLayoutComplete(false);
      return;
    }
    
    // Build tree from relations
    console.log("Building family tree with", relations.length, "relations");
    const builtTreeNodes = buildFamilyTree(relations);
    
    // Position nodes (immediately to avoid flash of unstyled content)
    const positionNodes = () => {
      try {
        const horizontalSpacing = isMobile ? 130 : 180; // Spacing between nodes horizontally
        const verticalSpacing = isMobile ? 100 : 150;   // Spacing between levels
        const startX = isMobile ? 300 : 400;            // Starting X position
        const startY = 100;                             // Starting Y position

        // Assign positions to each node
        const assignPositions = (
          node: TreeNode, 
          x = startX, 
          y = startY + (node.level * verticalSpacing),
          horizontalOffset = 0
        ) => {
          // Assign position to current node
          node.x = x + horizontalOffset;
          node.y = y;
          
          // Process children
          if (node.children.length > 0) {
            // Calculate total width needed for children
            const childrenWidth = (node.children.length - 1) * horizontalSpacing;
            
            // Start position for first child
            let startChildX = x - (childrenWidth / 2);
            
            // Position each child
            node.children.forEach((child, index) => {
              const childX = startChildX + (index * horizontalSpacing);
              assignPositions(child, childX, y + verticalSpacing, 0);
            });
          }
        };
        
        // Position each root node
        if (builtTreeNodes.length > 0) {
          // For multiple root nodes, space them apart
          builtTreeNodes.forEach((rootNode, index) => {
            const rootX = startX + (index * horizontalSpacing * 2);
            assignPositions(rootNode, rootX, startY);
          });
        }
        
        setTreeNodes(builtTreeNodes);
        console.log("Tree layout completed with", builtTreeNodes.length, "root nodes");
      } catch (error) {
        console.error("Error positioning tree nodes:", error);
      } finally {
        setLayoutComplete(true);
      }
    };
    
    // Position nodes (with a small delay to let the UI update)
    setTimeout(positionNodes, 10);
    
  }, [relations, isMobile]);
  
  return { treeNodes, layoutComplete, calculateMaxLevel };
}
