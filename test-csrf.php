<?php
/**
 * Script de test pour diagnostiquer les problèmes CSRF
 * Usage: php test-csrf.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Test de configuration CSRF/Session\n";
echo "=====================================\n\n";

// Test 1: Configuration APP_URL
echo "1. APP_URL: " . config('app.url') . "\n";

// Test 2: Configuration de session
echo "2. Session Driver: " . config('session.driver') . "\n";
echo "3. Session Secure Cookie: " . (config('session.secure') ? 'true' : 'false') . "\n";
echo "4. Session Same Site: " . config('session.same_site') . "\n";

// Test 3: Configuration Sanctum
echo "5. Sanctum Stateful Domains: " . implode(', ', config('sanctum.stateful')) . "\n";

// Test 4: Test de génération de token CSRF
try {
    $token = csrf_token();
    echo "6. Token CSRF généré: " . substr($token, 0, 10) . "...\n";
    echo "   ✅ Génération de token CSRF OK\n";
} catch (Exception $e) {
    echo "   ❌ Erreur génération token CSRF: " . $e->getMessage() . "\n";
}

// Test 5: Test de session
try {
    session()->start();
    echo "7. Session ID: " . substr(session()->getId(), 0, 10) . "...\n";
    echo "   ✅ Session OK\n";
} catch (Exception $e) {
    echo "   ❌ Erreur session: " . $e->getMessage() . "\n";
}

echo "\n🎯 Recommandations:\n";
echo "==================\n";

$isHttps = parse_url(config('app.url'), PHP_URL_SCHEME) === 'https';

if ($isHttps && !config('session.secure')) {
    echo "⚠️  HTTPS détecté mais SESSION_SECURE_COOKIE=false\n";
    echo "   Ajoutez: SESSION_SECURE_COOKIE=true dans .env\n";
}

if (!$isHttps && config('session.secure')) {
    echo "⚠️  HTTP détecté mais SESSION_SECURE_COOKIE=true\n";
    echo "   Ajoutez: SESSION_SECURE_COOKIE=false dans .env\n";
}

$domain = parse_url(config('app.url'), PHP_URL_HOST);
$statefulDomains = config('sanctum.stateful');

if (!in_array($domain, $statefulDomains)) {
    echo "⚠️  Domaine '$domain' pas dans SANCTUM_STATEFUL_DOMAINS\n";
    echo "   Ajoutez: SANCTUM_STATEFUL_DOMAINS=$domain,localhost,127.0.0.1\n";
}

echo "\n✅ Test terminé!\n";
