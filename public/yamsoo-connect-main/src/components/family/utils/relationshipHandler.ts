
import { FamilyRelation } from "@/types/family";
import { TreeNode } from "../types";
import { setNodePositioning } from "./nodePositioning";
import { createTemporaryParentNode, createTemporaryChildNode, findNodesWithChildren } from "./nodeCreator";
import { extractProfileFromRelation } from "./mappers";

/**
 * Establishes relationships between nodes
 */
export function establishRelationships(relations: FamilyRelation[], nodes: Map<string, TreeNode>, currentUserId: string | null): void {
  if (!currentUserId) return;
  
  const currentUserNode = nodes.get(currentUserId);
  if (!currentUserNode) return;
  
  // Process each relation to create connections
  relations.forEach(relation => {
    const profile = extractProfileFromRelation(relation);
    if (!profile) return;
    
    const relatedNodeId = profile.id;
    const relatedNode = nodes.get(relatedNodeId);
    
    if (!relatedNode) return;
    
    // Set up family relationships based on relation type
    setUpFamilyConnection(relation.relation_type, relatedNode, currentUserNode, nodes);
  });
  
  // Perform a second pass to ensure spouse connections are established properly
  relations.forEach(relation => {
    if (relation.relation_type === 'husband' || relation.relation_type === 'wife') {
      const profile = extractProfileFromRelation(relation);
      if (!profile) return;
      
      const relatedNodeId = profile.id;
      const relatedNode = nodes.get(relatedNodeId);
      const currentUserNode = nodes.get(currentUserId);
      
      if (!relatedNode || !currentUserNode) return;
      
      // Ensure they're at the same level for proper display
      relatedNode.level = currentUserNode.level;
      relatedNode.generation = currentUserNode.generation;
    }
  });
}

/**
 * Sets up connection between family members based on relation type
 */
export function setUpFamilyConnection(
  relationType: string, 
  relatedNode: TreeNode, 
  currentUserNode: TreeNode,
  nodes: Map<string, TreeNode>
): void {
  // Set node positioning based on relation type
  setNodePositioning(relatedNode, relationType);
  
  // Create parent-child connections based on relation type
  switch (relationType) {
    // Case where the related person is a parent of current user
    case 'father':
    case 'mother':
      if (!relatedNode.children.some(child => child.id === currentUserNode.id)) {
        relatedNode.children.push(currentUserNode);
      }
      break;
      
    // Case where the related person is a child of current user  
    case 'son':
    case 'daughter':
      if (!currentUserNode.children.some(child => child.id === relatedNode.id)) {
        currentUserNode.children.push(relatedNode);
      }
      break;
      
    // Handle spouse relationship
    case 'husband':
    case 'wife':
      // For spouse, we place them on the same level as the current user
      relatedNode.level = currentUserNode.level;
      relatedNode.generation = currentUserNode.generation;
      break;
      
    // Handle sibling relationship including half-siblings  
    case 'brother':
    case 'sister':
    case 'half_brother_maternal':
    case 'half_brother_paternal':
    case 'half_sister_maternal':
    case 'half_sister_paternal':
      // For siblings, find parents of current user
      const parents = findNodesWithChildren(nodes).filter(node => 
        node.children.some(child => child.id === currentUserNode.id)
      );
      
      // If a parent exists, make them also a parent of the sibling
      if (parents.length > 0) {
        parents.forEach(parent => {
          if (!parent.children.some(child => child.id === relatedNode.id)) {
            parent.children.push(relatedNode);
          }
        });
      }
      break;
      
    // Handle all types of uncle/aunt relationships
    case 'grandfather':
    case 'grandmother':
    case 'uncle':
    case 'aunt':
    case 'uncle_paternal':
    case 'uncle_maternal':
    case 'aunt_paternal':
    case 'aunt_maternal':
      // For grandparents and uncles/aunts, they need to be connected to parents
      const parents2 = findNodesWithChildren(nodes).filter(node => 
        node.children.some(child => child.id === currentUserNode.id)
      );
      
      if (parents2.length > 0) {
        parents2.forEach(parent => {
          if (!relatedNode.children.some(child => child.id === parent.id)) {
            relatedNode.children.push(parent);
          }
        });
      } else if (relationType.includes('grand')) {
        // If no parent exists yet, create a temporary parent node for grandparents
        const tempParentNode = createTemporaryParentNode(relatedNode, currentUserNode);
        nodes.set(tempParentNode.id, tempParentNode);
      }
      break;
      
    // Handle grandchild relationship
    case 'grandson':
    case 'granddaughter':
      // For grandchildren, they need to be connected via children of current user
      const children = currentUserNode.children;
      
      if (children.length > 0) {
        // Connect grandchild to one of the children
        const child = children[0];
        if (!child.children.some(grandchild => grandchild.id === relatedNode.id)) {
          child.children.push(relatedNode);
        }
      } else {
        // If no child exists yet, create a temporary child node
        const tempChildNode = createTemporaryChildNode(currentUserNode, relatedNode);
        nodes.set(tempChildNode.id, tempChildNode);
        currentUserNode.children.push(tempChildNode);
      }
      break;
      
    // Handle cousin relationships
    case 'cousin':
    case 'cousin_paternal_m':
    case 'cousin_maternal_m':
    case 'cousin_paternal_f':
    case 'cousin_maternal_f':
      // For cousins, try to find uncles/aunts and connect through them
      const unclesAunts = Array.from(nodes.values()).filter(node => 
        node.relation && (node.relation.includes("Oncle") || node.relation.includes("Tante"))
      );
      
      if (unclesAunts.length > 0) {
        // Connect cousin as a child of the first uncle/aunt
        const uncleAunt = unclesAunts[0];
        if (!uncleAunt.children.some(child => child.id === relatedNode.id)) {
          uncleAunt.children.push(relatedNode);
        }
      }
      break;
      
    // Handle nephew/niece relationships
    case 'nephew':
    case 'niece':
    case 'nephew_brother':
    case 'nephew_sister':
    case 'niece_brother':
    case 'niece_sister':
      // For nephews/nieces, try to find siblings and connect through them
      const siblings = Array.from(nodes.values()).filter(node => 
        node.relation && (node.relation.includes("Frère") || node.relation.includes("Sœur"))
      );
      
      if (siblings.length > 0) {
        // Connect nephew/niece as a child of the first sibling
        const sibling = siblings[0];
        if (!sibling.children.some(child => child.id === relatedNode.id)) {
          sibling.children.push(relatedNode);
        }
      }
      break;
  }
}
