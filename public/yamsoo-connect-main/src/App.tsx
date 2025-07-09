
import { useEffect } from "react";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import { Toaster } from "@/components/ui/toaster";
import { PrivateRoute } from "@/components/auth/PrivateRoute";
import { SidebarProvider } from "@/components/ui/sidebar";
import Index from "@/pages/Index";
import Auth from "@/pages/Auth";
import Dashboard from "@/pages/Dashboard";
import Family from "@/pages/Family";
import FamilyTree from "@/pages/FamilyTree";
import Messages from "@/pages/Messages";
import Networks from "@/pages/Networks";
import Notifications from "@/pages/Notifications";
import Suggestions from "@/pages/Suggestions";
import Admin from "@/pages/Admin";
import NotFound from "@/pages/NotFound";
import "./theme/layout.css";

export default function App() {
  // Tenter de masquer le badge Lovable avec du CSS
  useEffect(() => {
    const style = document.createElement('style');
    style.textContent = `
      .lov-badge, [class*="lovable-badge"], [id*="lovable-badge"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
      }
    `;
    document.head.appendChild(style);
    
    return () => {
      document.head.removeChild(style);
    };
  }, []);

  return (
    <Router>
      <SidebarProvider>
        <Routes>
          <Route path="/" element={<Index />} />
          <Route path="/auth" element={<Auth />} />
          <Route
            path="/dashboard"
            element={
              <PrivateRoute>
                <Dashboard />
              </PrivateRoute>
            }
          />
          <Route
            path="/famille"
            element={
              <PrivateRoute>
                <Family />
              </PrivateRoute>
            }
          />
          <Route
            path="/famille/arbre"
            element={
              <PrivateRoute>
                <FamilyTree />
              </PrivateRoute>
            }
          />
          <Route
            path="/messagerie"
            element={
              <PrivateRoute>
                <Messages />
              </PrivateRoute>
            }
          />
          <Route
            path="/notifications"
            element={
              <PrivateRoute>
                <Notifications />
              </PrivateRoute>
            }
          />
          <Route
            path="/suggestions"
            element={
              <PrivateRoute>
                <Suggestions />
              </PrivateRoute>
            }
          />
          <Route
            path="/networks"
            element={
              <PrivateRoute>
                <Networks />
              </PrivateRoute>
            }
          />
          <Route
            path="/admin"
            element={
              <PrivateRoute>
                <Admin />
              </PrivateRoute>
            }
          />
          <Route
            path="/yamsoo"
            element={
              <PrivateRoute>
                <Dashboard />
              </PrivateRoute>
            }
          />
          <Route path="*" element={<NotFound />} />
        </Routes>
      </SidebarProvider>
      <Toaster />
    </Router>
  );
}
