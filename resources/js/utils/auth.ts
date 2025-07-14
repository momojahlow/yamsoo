/**
 * Fonction utilitaire pour gérer la déconnexion avec Laravel Breeze
 */
export const logout = async (): Promise<boolean> => {
  try {
    const response = await fetch('/logout', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'application/json',
      },
    });

    if (response.ok) {
      // Redirection vers la page d'accueil après déconnexion réussie
      window.location.href = "/";
      return true;
    } else {
      console.error('Erreur lors de la déconnexion:', response.status, response.statusText);
      return false;
    }
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error);
    return false;
  }
};
