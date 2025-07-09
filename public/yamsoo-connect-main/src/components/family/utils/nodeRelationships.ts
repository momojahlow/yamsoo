
import { TreeNode } from "../types";

/**
 * Connects a parent node to a child node
 */
export function connectParentToChild(parent: TreeNode, child: TreeNode): void {
  if (!parent.children.some(existingChild => existingChild.id === child.id)) {
    parent.children.push(child);
  }
}

/**
 * Finds parent nodes in the tree
 */
export function findParentNodes(nodes: Map<string, TreeNode>): TreeNode[] {
  return Array.from(nodes.values()).filter(node => 
    (node.relation === 'Père' || node.relation === 'Mère') && node.level === 1
  );
}

/**
 * Finds sibling nodes in the tree
 */
export function findSiblingNodes(nodes: Map<string, TreeNode>): TreeNode[] {
  return Array.from(nodes.values()).filter(node => 
    (node.relation === 'Frère' || node.relation === 'Sœur') && node.level === 2
  );
}

/**
 * Finds child nodes in the tree
 */
export function findChildNodes(nodes: Map<string, TreeNode>): TreeNode[] {
  return Array.from(nodes.values()).filter(node => 
    (node.relation === 'Fils' || node.relation === 'Fille') && node.level === 3
  );
}

/**
 * Find root nodes for the tree (nodes without parents or at top level)
 */
export function findRootNodes(nodes: Map<string, TreeNode>): TreeNode[] {
  return Array.from(nodes.values()).filter(node => {
    return node.level === 0 || (node.level === 1 && !Array.from(nodes.values()).some(n => n.level === 0));
  });
}
