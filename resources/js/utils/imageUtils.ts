
/**
 * Utility functions for handling images
 */

/**
 * Check if a URL is an image based on its extension
 */
export const isImageUrl = (url: string): boolean => {
  const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'];
  const lowerCaseUrl = url.toLowerCase();
  return imageExtensions.some(ext => lowerCaseUrl.endsWith(ext));
};
