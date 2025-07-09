
import { TreeNode } from "../../types";

/**
 * Draws connections between parent and child nodes
 */
export function drawNodeConnections(
  ctx: CanvasRenderingContext2D, 
  node: TreeNode, 
  processedConnections: Set<string> = new Set()
) {
  // Safety check: ensure node.children exists before trying to iterate
  if (!node.children || !Array.isArray(node.children)) {
    return;
  }

  node.children.forEach(child => {
    // Skip circular references or nodes without proper structure
    if (!child || typeof child !== 'object' || 'message' in child) {
      return;
    }

    // Create a unique key for this connection to avoid duplicates
    const connectionKey = `${node.id}-${child.id}`;
    const reverseKey = `${child.id}-${node.id}`;
    
    // Skip if we've already processed this connection
    if (processedConnections.has(connectionKey) || processedConnections.has(reverseKey)) {
      return;
    }
    
    processedConnections.add(connectionKey);
    
    if (node.x !== undefined && node.y !== undefined && 
        child.x !== undefined && child.y !== undefined) {
      
      // Create a gradient for the connection line
      const gradient = ctx.createLinearGradient(node.x, node.y, child.x, child.y);
      gradient.addColorStop(0, '#9b87f5');
      gradient.addColorStop(1, '#a78bfa');
      
      // Draw a curved line from parent to child
      ctx.beginPath();
      ctx.strokeStyle = gradient;
      ctx.lineWidth = 3;
      
      const verticalOffset = 50;
      
      // Start from the bottom of the parent node
      const startX = node.x;
      const startY = node.y + verticalOffset;
      
      // End at the top of the child node
      const endX = child.x;
      const endY = child.y - verticalOffset;
      
      // Calculate middle points for the curve
      const midY = (startY + endY) / 2;
      
      // Draw a curve from parent to child with more pronounced curves
      ctx.moveTo(startX, startY);
      ctx.bezierCurveTo(
        startX, midY - 30,
        endX, midY - 30,
        endX, endY
      );
      
      ctx.stroke();
      
      // Add decorative elements at connection points
      ctx.fillStyle = '#9b87f5';
      
      // Start point decoration (circle)
      ctx.beginPath();
      ctx.arc(startX, startY, 4, 0, Math.PI * 2);
      ctx.fill();
      
      // End point decoration (circle)
      ctx.beginPath();
      ctx.arc(endX, endY, 4, 0, Math.PI * 2);
      ctx.fill();
      
      // Add a small dot at the middle of the curve for extra decoration
      const midX = (startX + endX) / 2;
      ctx.beginPath();
      ctx.arc(midX, midY - 30, 2, 0, Math.PI * 2);
      ctx.fill();
    }
    
    // Recursively draw connections for this child's children
    drawNodeConnections(ctx, child, processedConnections);
  });
}
