// Service Worker pour Yamsoo
// Version du cache
const CACHE_VERSION = 'yamsoo-v1.0.0';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;

// Ressources à mettre en cache immédiatement
const STATIC_ASSETS = [
  '/',
  '/dashboard',
  '/famille',
  '/offline.html', // Page hors ligne
  // Assets CSS et JS seront ajoutés dynamiquement
];

// Ressources à ne jamais mettre en cache
const NEVER_CACHE = [
  '/api/',
  '/logout',
  '/login',
  '/register',
  '/admin/',
];

// Installation du Service Worker
self.addEventListener('install', (event) => {
  console.log('[SW] Installation en cours...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[SW] Cache statique ouvert');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('[SW] Ressources statiques mises en cache');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[SW] Erreur lors de l\'installation:', error);
      })
  );
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
  console.log('[SW] Activation en cours...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            // Supprimer les anciens caches
            if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
              console.log('[SW] Suppression de l\'ancien cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Service Worker activé');
        return self.clients.claim();
      })
  );
});

// Interception des requêtes
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Ignorer les requêtes non-HTTP
  if (!request.url.startsWith('http')) {
    return;
  }
  
  // Ignorer les ressources à ne jamais mettre en cache
  if (NEVER_CACHE.some(path => url.pathname.startsWith(path))) {
    return;
  }
  
  // Stratégie Cache First pour les assets statiques
  if (isStaticAsset(request)) {
    event.respondWith(cacheFirst(request));
    return;
  }
  
  // Stratégie Network First pour les pages
  if (isPageRequest(request)) {
    event.respondWith(networkFirst(request));
    return;
  }
  
  // Stratégie par défaut : Network First
  event.respondWith(networkFirst(request));
});

// Vérifier si c'est un asset statique
function isStaticAsset(request) {
  const url = new URL(request.url);
  return url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/);
}

// Vérifier si c'est une requête de page
function isPageRequest(request) {
  return request.method === 'GET' && 
         request.headers.get('accept') && 
         request.headers.get('accept').includes('text/html');
}

// Stratégie Cache First
async function cacheFirst(request) {
  try {
    const cache = await caches.open(STATIC_CACHE);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      console.log('[SW] Ressource servie depuis le cache:', request.url);
      return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
      console.log('[SW] Ressource mise en cache:', request.url);
    }
    
    return networkResponse;
  } catch (error) {
    console.error('[SW] Erreur Cache First:', error);
    throw error;
  }
}

// Stratégie Network First
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
      console.log('[SW] Page mise en cache dynamiquement:', request.url);
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Réseau indisponible, tentative depuis le cache:', request.url);
    
    const cache = await caches.open(DYNAMIC_CACHE);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      console.log('[SW] Page servie depuis le cache:', request.url);
      return cachedResponse;
    }
    
    // Si c'est une page et qu'elle n'est pas en cache, servir la page hors ligne
    if (isPageRequest(request)) {
      const offlinePage = await cache.match('/offline.html');
      if (offlinePage) {
        return offlinePage;
      }
    }
    
    throw error;
  }
}

// Gestion des messages du client
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_VERSION });
  }
});

// Notification de mise à jour disponible
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'CHECK_UPDATE') {
    // Logique de vérification de mise à jour
    event.ports[0].postMessage({ 
      hasUpdate: false, 
      version: CACHE_VERSION 
    });
  }
});

console.log('[SW] Service Worker Yamsoo chargé, version:', CACHE_VERSION);
