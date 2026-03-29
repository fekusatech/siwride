Current Module: 3.1 & Dashboard UI

Last Completed Task: Finalized Admin Dashboard Sidebar UI polish (Toggles, Spacing, Stability).

Pending Tasks:
- [ ] 3.2 Forgot Password Implementation
- [ ] 3.3 Profile Management Implementation
- [ ] 3.4 Driver Registration & Verification Flow

Global Context:
- Frontend: Inertia v3 + Svelte 5.
- Layout: AdminLayout.svelte manages global sidebar/topbar state.
- Styles: Custom admin alignment styles are in AdminLayout.svelte :global block to avoid Tailwind v4 build-time parsing issues in component styles.

Next Step: Start implementing the Forgot Password flow (3.2).
