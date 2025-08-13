import * as React from "react"
import { cn } from "@/lib/utils"

const AnimatedCard = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement> & {
    hover?: boolean;
    glow?: boolean;
    gradient?: boolean;
  }
>(({ className, hover = true, glow = false, gradient = false, ...props }, ref) => (
  <div
    ref={ref}
    className={cn(
      "rounded-xl border bg-card text-card-foreground shadow-sm transition-all duration-300",
      hover && "hover:shadow-xl hover:scale-[1.02] hover:-translate-y-1",
      glow && "hover:shadow-2xl hover:shadow-orange-500/20",
      gradient && "bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900",
      "backdrop-blur-sm bg-white/80 dark:bg-gray-800/80",
      className
    )}
    {...props}
  />
))
AnimatedCard.displayName = "AnimatedCard"

const AnimatedCardHeader = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex flex-col space-y-1.5 p-6", className)}
    {...props}
  />
))
AnimatedCardHeader.displayName = "AnimatedCardHeader"

const AnimatedCardTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn("font-semibold leading-none tracking-tight", className)}
    {...props}
  />
))
AnimatedCardTitle.displayName = "AnimatedCardTitle"

const AnimatedCardDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <p
    ref={ref}
    className={cn("text-sm text-muted-foreground", className)}
    {...props}
  />
))
AnimatedCardDescription.displayName = "AnimatedCardDescription"

const AnimatedCardContent = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div ref={ref} className={cn("p-6 pt-0", className)} {...props} />
))
AnimatedCardContent.displayName = "AnimatedCardContent"

const AnimatedCardFooter = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex items-center p-6 pt-0", className)}
    {...props}
  />
))
AnimatedCardFooter.displayName = "AnimatedCardFooter"

export { 
  AnimatedCard, 
  AnimatedCardHeader, 
  AnimatedCardFooter, 
  AnimatedCardTitle, 
  AnimatedCardDescription, 
  AnimatedCardContent 
}
