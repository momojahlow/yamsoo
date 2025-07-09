
import { FamilyRelation } from "@/types/family";
import { TreeNode } from "../tree/types";
import { createNodesMap } from "./nodeCreator";
import { establishRelationships } from "./relationshipHandler";
import { findRootNodes } from "./rootNodeFinder";

/**
 * Builds a family tree from a list of family relations
 */
export function buildFamilyTree(relations: FamilyRelation[]): TreeNode[] {
  console.log("Building family tree with relations:", relations.length);
  
  if (!relations || relations.length === 0) {
    console.log("No relations provided to build tree");
    return [];
  }
  
  try {
    // Create nodes for all family members
    const { nodes, currentUserId } = createNodesMap(relations);
    
    if (nodes.size === 0) {
      console.warn("No nodes created from relations");
      return [];
    }
    
    console.log(`Created ${nodes.size} nodes from relations, current user ID: ${currentUserId}`);
    
    // Establish relationships between nodes
    establishRelationships(relations, nodes, currentUserId);
    
    // Find root nodes (nodes without parents or at top level)
    const rootNodes = findRootNodes(nodes);
    
    // If no root nodes are found but we have nodes, use the current user as root
    if (rootNodes.length === 0 && currentUserId && nodes.has(currentUserId)) {
      console.log("No root nodes found, using current user as root");
      const currentUserNode = nodes.get(currentUserId)!;
      return [currentUserNode];
    }
    
    console.log("Found", rootNodes.length, "root nodes");
    
    // Log first root node for debugging
    if (rootNodes.length > 0) {
      const firstRoot = rootNodes[0];
      console.log("First root node:", firstRoot.name, "relation:", firstRoot.relation);
    }
    
    return rootNodes;
  } catch (error) {
    console.error("Error building family tree:", error);
    return [];
  }
}
