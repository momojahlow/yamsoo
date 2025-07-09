
import { FamilyRelation } from "@/types/family";
import { TreeNode } from "../types";
import { createTreeNode, createCurrentUserNode, extractProfileFromRelation } from "./mappers";

/**
 * Creates a map of tree nodes from family relations
 */
export function createNodesMap(relations: FamilyRelation[]): { nodes: Map<string, TreeNode>, currentUserId: string | null } {
  const nodes = new Map<string, TreeNode>();
  
  // Find current user ID from the first relation's user_profile
  let currentUserId: string | null = null;
  if (relations.length > 0 && relations[0].user_profile) {
    currentUserId = relations[0].user_profile.id;
  }
  
  // First pass: create all nodes
  relations.forEach(relation => {
    // Create a node for the current user if it doesn't exist yet
    if (currentUserId && !nodes.has(currentUserId)) {
      const currentUser = relation.user_profile;
      if (currentUser) {
        nodes.set(currentUserId, createCurrentUserNode(currentUserId, currentUser));
      }
    }
    
    // Create a node for the related profile if it doesn't exist yet
    const profile = extractProfileFromRelation(relation);
    if (profile) {
      const nodeId = profile.id;
      if (!nodes.has(nodeId)) {
        nodes.set(nodeId, createTreeNode(nodeId, profile, relation.relation_type, relation.id));
      }
    }
  });
  
  return { nodes, currentUserId };
}

/**
 * Creates a temporary parent node to connect grandparent to current user
 */
export function createTemporaryParentNode(grandparent: TreeNode, currentUser: TreeNode): TreeNode {
  const tempParentId = `temp-parent-${grandparent.id}-${currentUser.id}`;
  const tempParent: TreeNode = {
    id: tempParentId,
    name: "Parent",
    relation: grandparent.relation === "Grand-père" ? "Père" : "Mère",
    relationId: "",
    children: [currentUser],
    level: 1, // Parents are level 1
    generation: -1,
    x: 0,
    y: 0
  };
  
  grandparent.children.push(tempParent);
  return tempParent;
}

/**
 * Creates a temporary child node to connect current user to grandchild
 */
export function createTemporaryChildNode(currentUser: TreeNode, grandchild: TreeNode): TreeNode {
  const tempChildId = `temp-child-${currentUser.id}-${grandchild.id}`;
  const tempChild: TreeNode = {
    id: tempChildId,
    name: "Enfant",
    relation: grandchild.relation === "Petit-fils" ? "Fils" : "Fille",
    relationId: "",
    children: [grandchild],
    level: 3, // Children are level 3
    generation: 1,
    x: 0,
    y: 0
  };
  
  return tempChild;
}

/**
 * Finds nodes that have children
 */
export function findNodesWithChildren(nodes: Map<string, TreeNode>): TreeNode[] {
  return Array.from(nodes.values()).filter(node => node.children.length > 0);
}
