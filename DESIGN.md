# Product Requirements Document (PRD): Siwride Driver App

## 1. Project Overview
**Project Name:** Siwride Mobile (Driver Edition)  
**Objective:** A premium mobile application for drivers in Bali to manage bookings, track earnings, and provide real-time location updates.  
**Platform:** Mobile (Designed for Google Stitch / Flutter / React Native)  
**Target Audience:** Professional Drivers in Bali.

---

## 2. Design System & Aesthetics (The "Vibe")
- **Style:** Modern-Premium, Clean, and High-Contrast.
- **Color Palette:**
    - **Primary:** `#E52029` (Siwride Red) - for actions and branding.
    - **Background:** `#F8FAFD` (Soft White) for light mode / `#121212` for dark mode.
    - **Accent:** `#FFAB00` (Amber) for ratings and highlights.
    - **Success:** `#28A745` (Green) for completed jobs.
- **Typography:** Inter or Outfit (Modern Sans-Serif).
- **UI Elements:** Glassmorphism effects on cards, subtle drop shadows, and rounded corners (16px+).

---

## 3. User Journeys & App Structure

### Screen 1: Welcome & Authentication
- **Purpose:** Onboarding and secure access.
- **UI Components:**
    - Visual Hero Image (Bali Scenery + Car).
    - Tabbed Login/Register.
    - Fields: Email, Password, Device Name.
    - Action: "Login" button with loading state.
    - Link: "Register as Driver" (Navigate to multi-step registration).

### Screen 2: Driver Registration (Pending State)
- **Purpose:** Collect driver documents for admin approval.
- **UI Components:**
    - Form fields: Firstname, Lastname, Phone, Email.
    - Document Upload placeholders: NID/KTP, SIM, Profile Photo.
    - Status Card: "Awaiting Admin Approval" with a friendly illustration.

### Screen 3: Main Dashboard (Job Marketplace)
- **Purpose:** View available "Shared Jobs" that any driver can take.
- **UI Components:**
    - **Top Bar:** Driver Profile Avatar + Welcome Message.
    - **Job Tabs:** [Available Jobs] | [My Active Rides].
    - **Shared Job Cards:**
        - Route: "Airport (DPS) → Ubud".
        - Date/Time: "Today, 14:00".
        - Price: "Rp 350.000".
        - Action: "Take Job" button.
    - **Empty State:** Pulsing skeleton if no jobs are available.

### Screen 4: Active Job Details (The Workspace)
- **Purpose:** Execute a specific ride.
- **UI Components:**
    - **Status Stepper:** [Pending] → [OTW] → [Arrived] → [Finished].
    - **Guest Info:** (Note: Hidden until 4 PM for shared jobs).
    - **Map Integration:** Mini-map showing route.
    - **Action Buttons:**
        - "Update Status" (Large primary button).
        - "Upload Evidence" (Camera icon for Departure/Arrival photos).
    - **Navigation Link:** Button to open Google Maps/Waze.

### Screen 5: Earnings & Reports
- **Purpose:** Transparency on driver payout.
- **UI Components:**
    - **Summary Card:** Total Earnings this month.
    - **Period Filter:** (1st-15th or 16th-End of Month).
    - **Recap List:** List of completed non-cash jobs.
    - **Payout Info:** Next estimated payout date.

### Screen 6: Profile & Settings
- **Purpose:** Account management.
- **UI Components:**
    - Profile header with photo.
    - Toggle: "Online/Offline" status.
    - List Items: Change Password, Vehicle Info, Help Center, Logout.

---

## 4. API Integration Mapping (v1)
- `POST /auth/login`: Authentication.
- `GET /jobs/shared`: Fetch marketplace.
- `GET /jobs/my`: Fetch active assignments.
- `POST /jobs/{id}/take`: Claim a shared job.
- `PATCH /jobs/{id}/status`: Update job flow (OTW, Tiba, Selesai).
- `POST /jobs/{id}/evidence`: Upload proof of service.
- `POST /tracking/update`: Real-time GPS sync (background).

---

## 5. Success Metrics
- **Ease of Use:** Driver can accept a job in < 3 clicks.
- **Visibility:** Status updates are instantaneous.
- **Reliability:** Evidence upload works even with spotty Bali signal (offline-first ready UI).
