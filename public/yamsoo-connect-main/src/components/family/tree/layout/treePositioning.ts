
import { TreeNode } from "../types";

export function positionNodes(nodes: TreeNode[]): TreeNode[] {
  if (nodes.length === 0) return [];
  
  // Organize nodes by level
  const nodesByLevel: {[level: number]: TreeNode[]} = {};
  
  // Recursive function to collect all nodes by level
  const collectNodesByLevel = (node: TreeNode) => {
    if (!nodesByLevel[node.level]) {
      nodesByLevel[node.level] = [];
    }
    
    // Check if this is a spouse (husband or wife)
    if (node.relation === "Mari" || node.relation === "Épouse") {
      // Find current user
      const currentUser = findCurrentUser(nodes);
      if (currentUser) {
        // Force same level as current user
        node.level = currentUser.level;
      }
    }
    
    nodesByLevel[node.level].push(node);
    
    if (node.children && Array.isArray(node.children)) {
      node.children.forEach(child => {
        if (child && typeof child === 'object' && !('message' in child)) {
          collectNodesByLevel(child);
        }
      });
    }
  };
  
  // Collect all nodes by level
  nodes.forEach(root => collectNodesByLevel(root));
  
  // Use increased spacing values
  const horizontalSpacing = 220; // Increased for better horizontal separation
  const verticalSpacing = 220;   // Increased for better vertical separation
  
  // Process each level
  Object.entries(nodesByLevel).forEach(([levelStr, levelNodes]) => {
    const level = parseInt(levelStr);
    
    // Sort nodes by relation (important for visual hierarchy)
    levelNodes.sort((a, b) => {
      // Put "Moi" first
      if (a.relation === "Moi") return -1;
      if (b.relation === "Moi") return 1;
      
      // Put parents before other relations
      const aIsParent = a.relation.includes("Père") || a.relation.includes("Mère");
      const bIsParent = b.relation.includes("Père") || b.relation.includes("Mère");
      
      if (aIsParent && !bIsParent) return -1;
      if (!aIsParent && bIsParent) return 1;
      
      return 0;
    });
    
    // Calculate total width needed for this level
    const totalWidth = levelNodes.length * horizontalSpacing;
    
    // Center nodes horizontally based on level width
    const startX = (1600 - totalWidth) / 2 + horizontalSpacing / 2;
    
    // Position spouses together
    positionSpousesWithUser(levelNodes);
    
    // Assign positions to nodes
    levelNodes.forEach((node, index) => {
      if (!node.x || node.x === 0) {
        node.x = Math.max(120, startX + index * horizontalSpacing);
      }
      // Position vertically based on level
      node.y = level * verticalSpacing + 180;
    });
  });
  
  // Center the tree on the current user
  centerTreeOnUser(nodes);
  
  return nodes;
}

// Function to center the tree around the current user
function centerTreeOnUser(nodes: TreeNode[]): void {
  const currentUser = findCurrentUser(nodes);
  if (!currentUser) return;
  
  // Calculate center of the viewport
  const viewportWidth = 1600; // Increased width
  const targetX = viewportWidth / 2; // Target center position
  
  // Calculate offset to center the current user
  const offset = targetX - (currentUser.x || 0);
  
  if (Math.abs(offset) > 10) { // Only adjust if significant difference
    // Get all nodes in a flat array
    const allNodes: TreeNode[] = [];
    const collectAllNodes = (node: TreeNode) => {
      allNodes.push(node);
      if (node.children && Array.isArray(node.children)) {
        node.children.forEach(child => {
          if (child && typeof child === 'object' && !('message' in child)) {
            collectAllNodes(child);
          }
        });
      }
    };
    
    nodes.forEach(node => collectAllNodes(node));
    
    // Shift all nodes to center the current user
    allNodes.forEach(node => {
      if (node.x !== undefined) {
        node.x += offset;
      }
    });
  }
}

// Function to find current user in nodes
function findCurrentUser(nodes: TreeNode[]): TreeNode | undefined {
  const findUser = (node: TreeNode): TreeNode | undefined => {
    if (node.relation === "Moi") {
      return node;
    }
    
    if (!node.children || !Array.isArray(node.children)) return undefined;
    
    for (const child of node.children) {
      if (!child || typeof child !== 'object' || 'message' in child) continue;
      const found = findUser(child);
      if (found) return found;
    }
    
    return undefined;
  };
  
  for (const root of nodes) {
    const found = findUser(root);
    if (found) return found;
  }
  
  return undefined;
}

// Function to position spouses next to the current user
function positionSpousesWithUser(nodes: TreeNode[]): void {
  const currentUser = nodes.find(node => node.relation === "Moi");
  if (!currentUser) return;
  
  const spouses = nodes.filter(node => 
    (node.relation === "Mari" || node.relation === "Épouse")
  );
  
  if (spouses.length > 0) {
    // Position spouses to the right of the current user
    spouses.forEach((spouse) => {
      // Position right next to user with fixed spacing
      spouse.x = (currentUser.x || 0) + 200; // Increased spouse spacing for better readability
      spouse.y = currentUser.y; // Same vertical level
    });
  }
}
