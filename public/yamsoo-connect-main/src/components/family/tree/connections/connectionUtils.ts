
import { TreeNode } from "../../types";

/**
 * Traverses the tree and returns all nodes
 */
export function traverseTree(node: TreeNode): TreeNode[] {
  const result = [node];
  node.children.forEach(child => {
    result.push(...traverseTree(child));
  });
  return result;
}

/**
 * Finds spouse connections in the tree
 */
export function findSpouses(node: TreeNode, allNodes: TreeNode[]): [TreeNode, TreeNode][] {
  const spouses: [TreeNode, TreeNode][] = [];
  
  // Chercher les relations de conjoint (Moi et Mari/Épouse)
  if (node.relation === "Moi") {
    allNodes.forEach(otherNode => {
      if ((otherNode.relation === "Mari" || otherNode.relation === "Épouse") && 
          otherNode.level === node.level) {
        console.log(`Found spouse connection: ${node.name} - ${otherNode.name}`);
        // Assurer un ordre cohérent pour les connexions
        spouses.push([node, otherNode]);
      }
    });
  }
  
  // Rechercher également parmi les enfants
  node.children.forEach(child => {
    spouses.push(...findSpouses(child, allNodes));
  });
  
  return spouses;
}
