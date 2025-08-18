<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SystemController extends Controller
{
    /**
     * Afficher les informations système
     */
    public function info(Request $request): Response
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];

        return Inertia::render('Admin/System/Info', [
            'systemInfo' => $systemInfo
        ]);
    }

    /**
     * Afficher les logs système
     */
    public function logs(Request $request): Response
    {
        return Inertia::render('Admin/System/Logs', [
            'logs' => []
        ]);
    }

    /**
     * Vider le cache
     */
    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            
            return back()->with('success', 'Cache vidé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du vidage du cache: ' . $e->getMessage()]);
        }
    }

    /**
     * Activer le mode maintenance
     */
    public function enableMaintenance(Request $request)
    {
        try {
            Artisan::call('down');
            return back()->with('success', 'Mode maintenance activé.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'activation du mode maintenance: ' . $e->getMessage()]);
        }
    }

    /**
     * Désactiver le mode maintenance
     */
    public function disableMaintenance(Request $request)
    {
        try {
            Artisan::call('up');
            return back()->with('success', 'Mode maintenance désactivé.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la désactivation du mode maintenance: ' . $e->getMessage()]);
        }
    }

    /**
     * Afficher la configuration système
     */
    public function config(Request $request): Response
    {
        return Inertia::render('Admin/System/Config', [
            'config' => []
        ]);
    }

    /**
     * Mettre à jour la configuration système
     */
    public function updateConfig(Request $request)
    {
        // TODO: Implémenter la mise à jour de configuration
        return back()->with('success', 'Configuration mise à jour avec succès.');
    }
}
