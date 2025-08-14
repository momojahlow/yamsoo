/**
 * Fonction utilitaire pour gérer la déconnexion avec Laravel Breeze
 * Utilise la méthode recommandée avec FormData pour éviter les erreurs CSRF
 */
export const logout = async (): Promise<boolean> => {
  try {
    // Méthode 1: Essayer avec FormData (recommandé pour Laravel)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
      console.error('Token CSRF non trouvé, redirection...');
      window.location.href = "/";
      return false;
    }

    // Créer un FormData avec le token CSRF
    const formData = new FormData();
    formData.append('_token', csrfToken);

    const response = await fetch('/logout', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
    });

    if (response.ok || response.redirected) {
      // Déconnexion réussie
      window.location.href = "/";
      return true;
    } else if (response.status === 419) {
      console.warn('Token CSRF expiré, tentative de récupération...');
      // Essayer de récupérer un nouveau token
      await refreshCSRFToken();
      return false;
    } else {
      console.error('Erreur lors de la déconnexion:', response.status, response.statusText);
      // En cas d'erreur, forcer la déconnexion côté client
      clearClientSession();
      return false;
    }
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error);
    // En cas d'erreur réseau, forcer la déconnexion côté client
    clearClientSession();
    return false;
  }
};

/**
 * Rafraîchir le token CSRF
 */
async function refreshCSRFToken(): Promise<void> {
  try {
    const response = await fetch('/csrf-token', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    });

    if (response.ok) {
      const data = await response.json();
      const metaTag = document.querySelector('meta[name="csrf-token"]');
      if (metaTag && data.csrf_token) {
        metaTag.setAttribute('content', data.csrf_token);
      }
    }
  } catch (error) {
    console.error('Erreur lors du rafraîchissement du token CSRF:', error);
  }
}

/**
 * Nettoyer la session côté client et rediriger
 */
function clearClientSession(): void {
  // Supprimer les données de session locales
  localStorage.clear();
  sessionStorage.clear();

  // Supprimer les cookies de session (si possible)
  document.cookie.split(";").forEach(function(c) {
    document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
  });

  // Rediriger vers la page d'accueil
  window.location.href = "/";
}
