
import { TreeNode } from "../../types";

/**
 * Draws connections between spouse nodes
 */
export function drawSpouseConnections(
  ctx: CanvasRenderingContext2D,
  spouseConnections: [TreeNode, TreeNode][]
) {
  console.log(`Drawing ${spouseConnections.length} spouse connections`);
  
  // Draw horizontal lines between spouses
  spouseConnections.forEach(([node1, node2]) => {
    if (node1.x !== undefined && node1.y !== undefined && 
        node2.x !== undefined && node2.y !== undefined) {
      
      console.log(`Drawing connection between ${node1.name} (${node1.x}, ${node1.y}) and ${node2.name} (${node2.x}, ${node2.y})`);
      
      ctx.beginPath();
      ctx.strokeStyle = '#f87171';  // Red color for spouse connections
      ctx.lineWidth = 3;
      ctx.setLineDash([8, 4]);
      
      // Calculer les points de départ et d'arrivée basés sur la position des nœuds
      const startX = node1.x < node2.x ? node1.x + 75 : node1.x - 75;
      const endX = node1.x < node2.x ? node2.x - 75 : node2.x + 75;
      
      ctx.moveTo(startX, node1.y);
      ctx.lineTo(endX, node2.y);
      
      ctx.stroke();
      
      // Add heart symbol between spouses
      const heartX = (node1.x + node2.x) / 2;
      const heartY = node1.y;
      
      ctx.fillStyle = '#f87171';
      ctx.beginPath();
      
      // Draw a heart shape
      ctx.moveTo(heartX, heartY + 5);
      ctx.bezierCurveTo(
        heartX - 5, heartY, 
        heartX - 10, heartY - 5,
        heartX, heartY - 10
      );
      ctx.bezierCurveTo(
        heartX + 10, heartY - 5,
        heartX + 5, heartY,
        heartX, heartY + 5
      );
      
      ctx.fill();
      
      // Reset style for other connections
      ctx.strokeStyle = '#9b87f5';
      ctx.lineWidth = 2.5;
      ctx.setLineDash([]);
    }
  });
}
