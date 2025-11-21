# eBoto - Electronic Voting System

A full-stack electronic voting application built with Laravel and Vue.js, designed for secure and efficient election management.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel (PHP) — Controllers, Services, Eloquent ORM |
| **Frontend** | Vue 3 + TypeScript with Inertia.js (SPA experience) |
| **Authentication** | Laravel Fortify (server-side auth with 2FA support) |
| **Database** | MariaDB |
| **Styling** | Vue components with reusable UI primitives |
| **Build Tools** | Vite, npm (frontend), Composer (PHP) |
| **SSR** | Server-side rendering support via `ssr.ts` |
| **Local Dev** | XAMPP / Apache + PHP (Windows) |

---

## Getting Started

### Prerequisites

- PHP 8.x
- Composer
- Node.js & npm
- MariaDB / MySQL
- XAMPP (or any Apache + PHP setup)
- Git Bash https://git-scm.com/install/windows

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/ychine/e-boto
cd eboto

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Copy environment file and configure
cp .env.example .env
# Edit .env with your database credentials, mail settings, etc.

# 5. Generate application key
php artisan key:generate

# 6. Run database migrations and seeders
php artisan migrate
php artisan db:seed

# 7. Build frontend assets
npm run dev
# Or for production: npm run build

# 8. Start the development server
php artisan serve
```

> **Note:** You can also import `eboto_db.sql` directly into MariaDB to restore a database snapshot.

---

## Project Structure Overview

```
eboto/
├── app/                    # Backend application code
│   ├── Http/
│   │   ├── Controllers/    # Request handlers (Admin, Voter, Auth)
│   │   └── Middleware/     # Request filtering (auth, permissions)
│   ├── Models/             # Eloquent models (User, Election, Vote, etc.)
│   └── Providers/          # Service providers and bootstrapping
│
├── database/
│   ├── migrations/         # Database schema definitions
│   ├── seeders/            # Test/demo data
│   └── factories/          # Model factories for testing
│
├── resources/
│   ├── js/                 # Frontend source code
│   │   ├── actions/        # API request helpers (mirrors backend controllers)
│   │   ├── components/     # Reusable Vue components
│   │   ├── composables/    # Vue composition API utilities
│   │   ├── layouts/        # Page layout templates
│   │   ├── pages/          # Inertia page components
│   │   └── routes/         # Frontend route helpers
│   └── views/              # Blade templates (Inertia root, emails)
│
├── routes/
│   └── web.php             # Web routes mapping URLs to controllers
│
├── eboto_db.sql            # Database dump for quick setup
├── .env                    # Environment configuration (not committed)
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
└── artisan                 # Laravel CLI entrypoint
```

---

## Key Configuration Files

| File | Purpose |
|------|---------|
| `.env` | Environment-specific config (DB, mail, keys). Create from `.env.example` |
| `composer.json` | PHP package dependencies. Run `composer install` to set up |
| `package.json` | Frontend dependencies and build scripts. Run `npm install` |
| `routes/web.php` | Defines all web routes and maps them to controllers |

---

## Backend Architecture

### Models (`app/Models/`)

Eloquent models represent database tables and define relationships:

- `User` — System users (admins and voters)
- `Election` — Election entities with status and configuration
- `Position` — Positions within an election
- `Candidate` — Candidates running for positions
- `Vote` — Cast ballots (encrypted/secured)

### Controllers (`app/Http/Controllers/`)

Handle HTTP requests and return responses. Organized by user role:

**Admin Controllers:**
- `DashboardController` — Admin overview and statistics
- `ElectionController` — CRUD operations for elections
- `PositionController` — Manage positions within elections
- `CandidateController` — Manage candidates
- `VoterController` — Import/export and manage voters
- `AuditLogController` — View system audit logs

**Voter Controllers:**
- `DashboardController` — Voter-specific overview
- `VoteController` — Ballot submission and validation

### Middleware & Providers (`app/Http/Middleware/`, `app/Providers/`)

- Authentication guards and permission checks
- Service registration and app bootstrapping
- Policy definitions for authorization

---

## Frontend Architecture

The frontend is a Vue 3 + TypeScript single-page application powered by Inertia.js.

### Entry Point (`resources/js/app.ts`)

Bootstraps the Vue application, registers global components and plugins, and mounts the Inertia SPA.

### Pages (`resources/js/pages/`)

Each page corresponds to an Inertia route:

**Admin Pages:**
- `Admin/Dashboard.vue` — Statistics and quick actions
- `Admin/Elections/Index.vue` — Election management
- `Admin/Positions/Index.vue` — Position management
- `Admin/Candidates/Index.vue` — Candidate management
- `Admin/Voters/Index.vue` — Voter management
- `Admin/AuditLogs/Index.vue` — Audit log viewer
- `Admin/Reports/Index.vue` — Reporting and exports

**Auth Pages:**
- `auth/Login.vue` — Login with 2FA support
- `auth/Register.vue` — User registration
- `auth/ForgotPassword.vue` — Password reset request
- `auth/ResetPassword.vue` — Set new password
- `auth/VerifyEmail.vue` — Email verification
- `auth/ConfirmPassword.vue` — Sensitive action confirmation

**User Pages:**
- `Dashboard.vue` — Voter dashboard
- `Landing.vue` — Public landing page
- `settings/*` — Profile, password, 2FA, and appearance settings

### Layouts (`resources/js/layouts/`)

Reusable page structures:

- `AdminLayout.vue` — Admin panel with sidebar navigation
- `AppLayout.vue` — Main application wrapper
- `AuthLayout.vue` — Authentication pages wrapper
- `auth/AuthCardLayout.vue`, `AuthSimpleLayout.vue`, `AuthSplitLayout.vue` — Auth page variants
- `settings/Layout.vue` — Settings section with tabs

### Components (`resources/js/components/`)

**App-Level Components:**
- `AppShell.vue` — Main app frame (header + sidebar + content)
- `AppHeader.vue` — Top navigation bar
- `AppSidebar.vue` / `AdminSidebar.vue` — Navigation sidebars
- `Breadcrumbs.vue` — Breadcrumb navigation
- `SimpleToast.vue` — Toast notifications

**Form & UI Components:**
- `AlertError.vue` — Validation error display
- `InputError.vue` — Inline field errors
- `DeleteUser.vue` — User deletion with confirmation

**2FA Components:**
- `TwoFactorSetupModal.vue` — QR code setup modal
- `TwoFactorRecoveryCodes.vue` — Recovery codes display

**UI Primitives (`resources/js/components/ui/`):**

| Folder | Components |
|--------|------------|
| `alert/` | Alert, AlertTitle, AlertDescription |
| `avatar/` | Avatar with fallback initials |
| `badge/` | Status badges and labels |
| `button/` | Primary button with loading states |
| `card/` | Card, CardHeader, CardContent, CardFooter |
| `checkbox/` | Styled checkbox input |
| `dialog/` | Accessible modal dialogs |
| `dropdown-menu/` | Context menus and dropdowns |
| `input/` | Text input component |
| `pin-input/` | OTP/2FA code entry |
| `sheet/` | Slide-over panels |
| `sidebar/` | Sidebar building blocks |
| `skeleton/` | Loading placeholders |
| `spinner/` | Loading spinner |
| `tooltip/` | Accessible tooltips |

### Actions (`resources/js/actions/`)

TypeScript helpers that mirror backend controllers for making API requests:

```
actions/
├── App/Http/Controllers/
│   ├── Admin/           # Admin API helpers
│   │   ├── ElectionController.ts
│   │   ├── CandidateController.ts
│   │   ├── PositionController.ts
│   │   ├── VoterController.ts
│   │   ├── AuditLogController.ts
│   │   └── DashboardController.ts
│   └── Voter/           # Voter API helpers
│       ├── DashboardController.ts
│       └── VoteController.ts
│
└── Laravel/Fortify/Http/Controllers/   # Auth API helpers
    ├── AuthenticatedSessionController.ts
    ├── RegisteredUserController.ts
    ├── TwoFactorAuthenticationController.ts
    └── ... (password reset, email verification, etc.)
```

### Composables (`resources/js/composables/`)

Vue Composition API utilities:

- `useAppearance.ts` — Theme management (light/dark mode)
- `useInitials.ts` — Generate avatar initials from names
- `useTwoFactorAuth.ts` — 2FA setup and management logic

### Routes (`resources/js/routes/`)

Frontend route path helpers for type-safe navigation:

- `admin/*` — Admin section routes
- `login/`, `register/` — Auth routes
- `profile/`, `password/` — User settings routes
- `votes/` — Voting routes

---

## Database

### Using Migrations (Recommended)

```bash
# Run all migrations
php artisan migrate

# Seed with demo data
php artisan db:seed

# Fresh install (drop all tables and re-migrate)
php artisan migrate:fresh --seed
```

### Using SQL Dump

Import `eboto_db.sql` directly into MariaDB:

```bash
mysql -u username -p eboto_db < eboto_db.sql
```

---

## Common Commands

```bash
# Start development server
php artisan serve

# Run frontend dev server with hot reload
npm run dev

# Build for production
npm run build

# Run migrations
php artisan migrate

# Create a new migration
php artisan make:migration create_table_name

# Create a new controller
php artisan make:controller ControllerName

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Authentication & Security

- **Laravel Fortify** handles authentication flows (login, registration, password reset)
- **Email Verification** for new user accounts
- **Password Confirmation** for sensitive operations
- Audit logging for admin actions


This project is for a client.
