
/**
 * Draws a grid pattern on the canvas
 */
export function drawGrid(
  ctx: CanvasRenderingContext2D, 
  width: number, 
  height: number,
  gridSize: number = 100 // Allow configurable grid size
) {
  console.log(`Drawing grid: ${width}x${height}, grid size: ${gridSize}`);
  
  // Ensure width and height are valid
  if (width <= 0 || height <= 0) {
    console.warn("Invalid canvas dimensions for grid", width, height);
    return;
  }
  
  ctx.save();
  ctx.strokeStyle = '#E2E8F0';
  ctx.lineWidth = 0.5;
  
  // Draw vertical lines
  for (let x = 0; x <= width; x += gridSize) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x, height);
    ctx.stroke();
  }
  
  // Draw horizontal lines
  for (let y = 0; y <= height; y += gridSize) {
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(width, y);
    ctx.stroke();
  }
  
  ctx.restore();
}
