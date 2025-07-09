
import { TreeNode } from "../types";

/**
 * Find root nodes for the tree (nodes without parents or at top level)
 */
export function findRootNodes(nodes: Map<string, TreeNode>): TreeNode[] {
  // First, find all nodes that are not children of any other node
  const childrenIds = new Set<string>();
  
  Array.from(nodes.values()).forEach(node => {
    node.children.forEach(child => {
      childrenIds.add(child.id);
    });
  });
  
  // Nodes that are not children of any other node are potential root nodes
  const potentialRoots = Array.from(nodes.values()).filter(node => 
    !childrenIds.has(node.id)
  );
  
  // If we have grandparents, they're the root
  const grandparents = potentialRoots.filter(node => 
    node.level === 0
  );
  
  if (grandparents.length > 0) {
    return grandparents;
  }
  
  // If we have parents, they're the root
  const parents = potentialRoots.filter(node => 
    node.level === 1
  );
  
  if (parents.length > 0) {
    return parents;
  }
  
  // If we don't have parents or grandparents, 
  // return any node that's not a child
  return potentialRoots;
}
