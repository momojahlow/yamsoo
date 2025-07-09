
import { useEffect, useRef } from "react";
import { ConnectionsProps } from "./tree/connections/types";
import { useIsMobile } from "@/hooks/use-mobile";
import { drawGrid } from "./tree/connections/GridLines";
import { findSpouses, traverseTree } from "./tree/connections/connectionUtils";
import { drawSpouseConnections } from "./tree/connections/SpouseConnections";
import { drawNodeConnections } from "./tree/connections/NodeConnections";

export function FamilyTreeConnections({ rootNodes }: ConnectionsProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const isMobile = useIsMobile();

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) {
      console.warn("Canvas ref is null");
      return;
    }

    const parent = canvas.parentElement;
    if (!parent) {
      console.warn("Canvas parent is null");
      return;
    }
    
    // Set canvas to full size of parent
    canvas.width = parent.clientWidth;
    canvas.height = parent.clientHeight;

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      console.warn("Could not get canvas context");
      return;
    }
    
    // Clear previous drawings
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw the grid background with adjusted size
    const gridSize = isMobile ? 50 : 100; // Smaller grid on mobile
    drawGrid(ctx, canvas.width, canvas.height, gridSize);
    
    if (rootNodes && rootNodes.length > 0) {
      console.log("Drawing connections for", rootNodes.length, "root nodes");
      
      // Get all nodes in a flat array, with additional safety check
      const allNodes = rootNodes.flatMap(node => 
        node ? traverseTree(node) : []
      );
      
      // If we have nodes, log some debug info
      if (allNodes.length > 0) {
        console.log(`Total nodes in tree: ${allNodes.length}`);
      }
      
      // Track processed connections to avoid duplicates
      const processedConnections = new Set<string>();
      
      // Draw hierarchical connections (parent-child) with safety checks
      rootNodes.forEach(node => {
        if (node) {
          drawNodeConnections(ctx, node, processedConnections);
        }
      });
      
      // Draw spouse connections with safety check
      const spouseConnections = rootNodes.flatMap(node => 
        node ? findSpouses(node, allNodes) : []
      );
      
      if (spouseConnections.length > 0) {
        console.log(`Drawing ${spouseConnections.length} spouse connections`);
        drawSpouseConnections(ctx, spouseConnections);
      }
    } else {
      console.warn("No root nodes available for connections");
    }
    
    // Redraw on resize
    const handleResize = () => {
      if (!canvas || !parent || !ctx) return;
      
      canvas.width = parent.clientWidth;
      canvas.height = parent.clientHeight;
      
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      // Redraw grid and connections
      const gridSize = isMobile ? 50 : 100;
      drawGrid(ctx, canvas.width, canvas.height, gridSize);
      
      if (rootNodes && rootNodes.length > 0) {
        const allNodes = rootNodes.flatMap(node => node ? traverseTree(node) : []);
        const processedConnections = new Set<string>();
        
        rootNodes.forEach(node => {
          if (node) {
            drawNodeConnections(ctx, node, processedConnections);
          }
        });
        
        const spouseConnections = rootNodes.flatMap(node => 
          node ? findSpouses(node, allNodes) : []
        );
        
        if (spouseConnections.length > 0) {
          drawSpouseConnections(ctx, spouseConnections);
        }
      }
    };
    
    window.addEventListener('resize', handleResize);
    
    // Observer for zoom changes
    const observer = new MutationObserver(handleResize);
    
    if (parent?.parentElement) {
      observer.observe(parent.parentElement, { attributes: true, attributeFilter: ['style'] });
    }
    
    return () => {
      window.removeEventListener('resize', handleResize);
      observer.disconnect();
    };
  }, [rootNodes, isMobile]);

  return (
    <canvas
      ref={canvasRef}
      className="absolute inset-0 w-full h-full pointer-events-none z-0"
      style={{ WebkitPrintColorAdjust: 'exact', printColorAdjust: 'exact' }}
    />
  );
}
