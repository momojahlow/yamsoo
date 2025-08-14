// Modern Dashboard Layouts
export { default as KuiDashboardLayout } from './kui-dashboard-layout';
export { default as StarterDashboardLayout } from './starter-dashboard-layout';
export { default as KwdDashboardLayout } from './kwd-dashboard-layout';

// Modern Auth Layout
export { default as KwdAuthLayout } from './kwd-auth-layout';

// Layout types for easy switching
export type ModernLayoutType = 'kui' | 'starter' | 'kwd';

// Layout configuration
export const MODERN_LAYOUTS = {
  kui: {
    name: 'KUI Dashboard',
    description: 'Professional layout with collapsible sidebar',
    component: 'KuiDashboardLayout',
    features: ['Dark mode', 'Search', 'Notifications', 'Profile dropdown']
  },
  starter: {
    name: 'Starter Dashboard',
    description: 'Clean and minimal layout with badges',
    component: 'StarterDashboardLayout',
    features: ['Gradient design', 'Navigation badges', 'User info panel']
  },
  kwd: {
    name: 'KWD Dashboard',
    description: 'Advanced layout with animations and collapsible sidebar',
    component: 'KwdDashboardLayout',
    features: ['Collapsible sidebar', 'Animations', 'Backdrop blur', 'Advanced styling']
  }
} as const;
