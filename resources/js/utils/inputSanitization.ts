
/**
 * Security utility functions for input sanitization
 */

export function sanitizeTextInput(input: string): string {
  if (!input || typeof input !== 'string') return '';
  
  // Remove potentially dangerous characters and scripts
  return input
    .trim()
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '') // Remove script tags
    .replace(/javascript:/gi, '') // Remove javascript: protocols
    .replace(/on\w+\s*=/gi, '') // Remove event handlers
    .slice(0, 1000); // Limit length to prevent DoS
}

export function sanitizeEmailInput(email: string): string {
  if (!email || typeof email !== 'string') return '';
  
  // Basic email validation and sanitization
  const sanitized = email.toLowerCase().trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
  return emailRegex.test(sanitized) ? sanitized : '';
}

export function sanitizeNumericInput(input: string | number): number | null {
  if (typeof input === 'number') return input;
  if (!input || typeof input !== 'string') return null;
  
  const numeric = parseFloat(input.replace(/[^0-9.-]/g, ''));
  return isNaN(numeric) ? null : numeric;
}

export function sanitizeHtmlContent(content: string): string {
  if (!content || typeof content !== 'string') return '';
  
  // Basic HTML sanitization - remove potentially dangerous tags
  return content
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
    .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
    .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
    .replace(/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/gi, '')
    .replace(/javascript:/gi, '')
    .replace(/on\w+\s*=/gi, '')
    .slice(0, 5000); // Limit content length
}
