
import { TreeNode } from "../../types";

export function traverseTree(node: TreeNode): TreeNode[] {
  const result: TreeNode[] = [node];
  for (const child of node.children) {
    result.push(...traverseTree(child));
  }
  return result;
}

export function getFlattenedUniqueNodes(treeNodes: TreeNode[]): TreeNode[] {
  // Get all nodes in a flat array
  const allNodes = treeNodes.flatMap(traverseTree);

  // Remove duplicate nodes based on ID
  return Array.from(
    new Map(allNodes.map(node => [node.id, node])).values()
  );
}
