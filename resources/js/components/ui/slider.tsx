
import * as React from "react"
import * as SliderPrimitive from "@radix-ui/react-slider"

import { cn } from "@/lib/utils"

interface SliderProps extends React.ComponentPropsWithoutRef<typeof SliderPrimitive.Root> {
  orientation?: "horizontal" | "vertical"
}

const Slider = React.forwardRef<
  React.ElementRef<typeof SliderPrimitive.Root>,
  SliderProps
>(({ className, orientation = "horizontal", ...props }, ref) => (
  <SliderPrimitive.Root
    ref={ref}
    className={cn(
      "relative flex touch-none select-none",
      orientation === "horizontal" 
        ? "w-full items-center" 
        : "h-full flex-col items-center",
      className
    )}
    orientation={orientation}
    {...props}
  >
    <SliderPrimitive.Track 
      className={cn(
        "relative overflow-hidden rounded-full",
        orientation === "horizontal" 
          ? "h-2 w-full grow bg-secondary" 
          : "h-full w-2 grow bg-secondary/20"
      )}
    >
      <SliderPrimitive.Range 
        className={cn(
          orientation === "horizontal" 
            ? "absolute h-full bg-primary" 
            : "absolute w-full bottom-0 bg-orange-500"
        )} 
      />
    </SliderPrimitive.Track>
    <SliderPrimitive.Thumb className={cn(
      "block rounded-full border-2 bg-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
      orientation === "horizontal" 
        ? "h-5 w-5 border-primary" 
        : "h-5 w-5 border-orange-500"
    )} />
  </SliderPrimitive.Root>
))
Slider.displayName = SliderPrimitive.Root.displayName

export { Slider }
