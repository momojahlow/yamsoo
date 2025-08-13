<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LayoutDemoController extends Controller
{
    /**
     * Display the layout demo page with layout selector.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('LayoutDemo');
    }

    /**
     * Display KUI Dashboard Layout demo.
     */
    public function kuiLayout(Request $request): Response
    {
        return Inertia::render('LayoutDemos/KuiDemo');
    }

    /**
     * Display Starter Dashboard Layout demo.
     */
    public function starterLayout(Request $request): Response
    {
        return Inertia::render('LayoutDemos/StarterDemo');
    }

    /**
     * Display KWD Dashboard Layout demo.
     */
    public function kwdLayout(Request $request): Response
    {
        return Inertia::render('LayoutDemos/KwdDemo');
    }
}
