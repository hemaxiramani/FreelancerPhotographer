# PhotoHire — Master Task List

**Created:** 2026-03-26
**Laravel Version:** 10.x (PHP 8.2) — Sanctum 3.3.3 pre-installed
**Laravel Path:** `D:\Software\xampp\htdocs\FreelancerPhotographer`
**Flutter Path:** `D:\Workspace\flutter\photoapp`
**Database:** `freelancer_photographers` (localhost phpMyAdmin)

---

## Phase 1: Laravel Backend — API

### 1.1 Project Setup
- [x] **T-001** Create Laravel 10.x project in `D:\Software\xampp\htdocs\FreelancerPhotographer`
- [x] **T-002** Configure `.env` — database (`freelancer_photographers`), app name, timezone, FCM key
- [x] **T-003** Install & configure Laravel Sanctum (API token auth) — pre-installed with Laravel 10
- [x] **T-004** Set up CORS configuration (allow Flutter web + mobile origins)
- [x] **T-005** Create base API response helper — `ApiResponse` trait with success/error/created/notFound/validationError
- [x] **T-006** Set up API route versioning (`/api/v1/` prefix) — 25 routes defined

### 1.2 Database Migrations (13 tables)
- [x] **T-007** Migration: `countries` table (id, name, iso2, status, created_at)
- [x] **T-008** Migration: `states` table (id, country_id FK, name, state_code, status, created_at)
- [x] **T-009** Migration: `cities` table (id, state_id FK, name, is_user_added, status, created_at)
- [x] **T-010** Migration: `users` table (id, name, email, phone, password, role, country_id FK, state_id FK, city_id FK, profile_photo, status, timestamps)
- [x] **T-011** Migration: `photographer_profiles` table (id, user_id FK, bio, experience, default_charge, instagram_link, facebook_link, portfolio_link, timestamps)
- [x] **T-012** Migration: `categories` table (id, name, status, created_at)
- [x] **T-013** Migration: `photographer_categories` pivot table (id, photographer_id FK, category_id FK, charge_per_day)
- [x] **T-014** Migration: `camera_kits` table (id, user_id FK, item_name, created_at)
- [x] **T-015** Migration: `work_cities` table (id, user_id FK, country_id FK, state_id FK, city_id FK, created_at)
- [x] **T-016** Migration: `device_tokens` table (id, user_id FK, device_name, device_type enum, fcm_token, access_token_id FK, last_active_at, timestamps)
- [x] **T-017** Migration: `hire_requests` table (id, photographer_id FK, event_date, event_type, country_id FK, state_id FK, city_id FK, note, status enum, timestamps)
- [x] **T-018** Migration: `notifications` table (id, target_type enum, title, message, sent_at)
- [x] **T-019** Migration: `notification_user` pivot table (id, notification_id FK, user_id FK, read_at nullable)
- [x] **T-020** Run all migrations and verify — 16 migrations ran successfully

### 1.3 Database Seeders & SQL Import
- [x] **T-021** Prepare `database/sql/countries.sql` — 250 countries from GeoNames
- [x] **T-022** Prepare `database/sql/states.sql` — 3,862 states from GeoNames
- [x] **T-023** Prepare `database/sql/cities.sql` — 33,337 cities (population > 15K) from GeoNames
- [x] **T-024** Import SQL files via MySQL CLI — all 3 imported successfully
- [x] **T-025** Create `AdminSeeder` — seed 1 admin user (admin@photohire.com / admin@123)
- [x] **T-026** Create `CategorySeeder` — seed 16 pre-defined categories
- [x] **T-027** Run seeders: `php artisan db:seed` — admin + 16 categories seeded

### 1.4 Models & Relationships (11 models)
- [x] **T-028** Model: `Country` (hasMany states, scopeActive)
- [x] **T-029** Model: `State` (belongsTo country, hasMany cities, scopeActive)
- [x] **T-030** Model: `City` (belongsTo state, scopeActive)
- [x] **T-031** Model: `User` (all relationships + scopes: photographers, active, blocked + helpers: isAdmin, isPhotographer, isActive)
- [x] **T-032** Model: `PhotographerProfile` (belongsTo user)
- [x] **T-033** Model: `Category` (belongsToMany users via pivot, scopeActive)
- [x] **T-034** Model: `CameraKit` (belongsTo user)
- [x] **T-035** Model: `WorkCity` (belongsTo user/country/state/city)
- [x] **T-036** Model: `DeviceToken` (belongsTo user)
- [x] **T-037** Model: `HireRequest` (belongsTo user/country/state/city + status scopes)
- [x] **T-038** Model: `Notification` (belongsToMany users via pivot)

### 1.5 API — Authentication & Device Management
- [x] **T-039** `AuthController@register` — creates user + photographer_profile + sanctum token + device_token
- [x] **T-040** `AuthController@login` — validates + checks blocked status + creates token + device_token
- [x] **T-041** `AuthController@logout` — deletes device_token + revokes sanctum token
- [x] **T-042** `DeviceController@index` — lists own active devices
- [x] **T-043** `DeviceController@destroy` — remote logout (prevents deleting current device)
- [x] **T-044** `AuthController@updateFcmToken` — updates fcm_token on current device_token row
- [x] **T-045** Auth middleware setup — Sanctum `auth:sanctum` on all protected routes
- [x] **T-046** Validation & error handling for all auth endpoints

### 1.6 API — Locations (Public, No Auth)
- [x] **T-047** `LocationController@countries` — all active countries ordered by name
- [x] **T-048** `LocationController@states` — states for a country
- [x] **T-049** `LocationController@cities` — cities for a state (searchable, limit 100)

### 1.7 API — Photographer Profile
- [x] **T-050** `ProfileController@show` — full profile with all eager-loaded relationships
- [x] **T-051** `ProfileController@update` — updates user fields + photographer profile fields
- [x] **T-052** `ProfileController@updatePhoto` — upload/replace profile photo to storage/public

### 1.8 API — Categories
- [x] **T-053** `CategoryController@index` — all active categories
- [x] **T-054** `CategoryController@syncMyCategories` — sync categories with charge_per_day pivot

### 1.9 API — Camera Kit
- [x] **T-055** `CameraKitController@index` — own kit items
- [x] **T-056** `CameraKitController@store` — add item
- [x] **T-057** `CameraKitController@destroy` — remove own item

### 1.10 API — Work Cities
- [x] **T-058** `WorkCityController@index` — own work cities with country/state/city names
- [x] **T-059** `WorkCityController@store` — add work city (with duplicate check)
- [x] **T-060** `WorkCityController@destroy` — remove own work city

### 1.11 API — Hire Requests (Photographer Side)
- [x] **T-061** `HireRequestController@index` — own requests with status filter & pagination
- [x] **T-062** `HireRequestController@show` — single request detail with location names
- [x] **T-063** `HireRequestController@respond` — accept or decline (only pending requests)

### 1.12 API — Notifications
- [x] **T-064** `NotificationController@index` — own notifications paginated with read_at
- [x] **T-065** `NotificationController@markRead` — mark notification as read

### 1.13 FCM Service
- [x] **T-066** Create `FcmService` — send push via FCM legacy HTTP API
- [x] **T-067** FCM integration: `sendToUser` sends to ALL device tokens (multi-device)
- [ ] **T-068** Trigger FCM on: hire request sent, photographer accepts/declines, custom notification, new registration

### 1.14 API Route File & Testing
- [x] **T-069** Define all API routes in `routes/api.php` — 25 routes with proper grouping & middleware
- [ ] **T-070** Test all API endpoints with Postman / Insomnia (create collection)

---

## Phase 2: Laravel Admin Panel (Web Portal — Blade)

### 2.1 Admin Setup & Layout
- [x] **T-071** Admin auth middleware (web guard, session-based) — separate from API Sanctum
- [x] **T-072** Admin login page (Blade) — GET/POST `/admin/login`
- [x] **T-073** Admin layout template (Blade) — sidebar nav, header, content area (Bootstrap 5)
- [x] **T-074** Admin logout — POST `/admin/logout`

### 2.2 Dashboard
- [x] **T-075** Dashboard page — stats: total photographers, active/blocked, pending requests, today's registrations

### 2.3 Photographer Management
- [x] **T-076** Photographer list page — table view with filters (country, state cascading, category, name search, status)
- [x] **T-077** Photographer detail page — full profile: bio, experience, categories+charges, camera kit, work cities, social links, status
- [x] **T-078** Block / Unblock photographer action
- [x] **T-079** Remove (delete) photographer action

### 2.4 Hire Requests
- [x] **T-080** Send hire request form — select photographer, event date, event type, location (Country → State → City cascading dropdowns), note
- [x] **T-081** Hire requests list page — all sent requests with status filter (Pending / Accepted / Declined / Invalidated)
- [x] **T-082** Invalidate request action — admin cancels a pending request

### 2.5 Notifications
- [x] **T-083** Send notification form — target selection (all / specific photographer checkboxes) + title + message → trigger FCM
- [x] **T-084** Notification history page — all sent notifications with targets, recipients count, timestamps

### 2.6 Location Management
- [x] **T-085** Browse locations page — view countries → states → cities (drill-down breadcrumb navigation)
- [x] **T-086** Add missing city form — name + select state → insert with `is_user_added = true`
- [x] **T-087** Activate / deactivate country, state, or city

### 2.7 Category Management
- [x] **T-088** Category list page — all categories with photographer count
- [x] **T-089** Add new category form
- [x] **T-090** Edit category name (inline edit with pencil icon)
- [x] **T-091** Activate / deactivate category

### 2.8 Admin Route File
- [x] **T-092** Define all admin web routes in `routes/web.php` with admin middleware group — 55 total routes verified

---

## Phase 3: Flutter App — Design

### 3.1 Design System & Theme
- [ ] **T-093** Define app theme — brand color #588161, typography, spacing, border radius
- [ ] **T-094** Design Splash screen
- [ ] **T-095** Design Register screen (6 fields + Country → State → City cascading dropdowns)
- [ ] **T-096** Design Login screen
- [ ] **T-097** Design Home / Dashboard screen (request count, notifications, profile summary)
- [ ] **T-098** Design My Profile screen (view all fields, completion indicator)
- [ ] **T-099** Design Edit Profile screen (all 7 profile fields — categories, kit, charges, work cities, links)
- [ ] **T-100** Design Hire Requests list screen (cards with status badges)
- [ ] **T-101** Design Hire Request detail screen (date, event, location, note, accept/decline buttons)
- [ ] **T-102** Design Notifications screen (list with read/unread)
- [ ] **T-103** Design reusable components — location picker, category chips, kit list, charge cards

---

## Phase 4: Flutter App — Development

### 4.1 Project Setup
- [ ] **T-104** Initialize Flutter project in `D:\Workspace\flutter\photoapp` (web + android + ios)
- [ ] **T-105** Configure `pubspec.yaml` — add all dependencies (dio, provider, firebase_messaging, flutter_secure_storage, image_picker, url_launcher, dropdown_search, device_info_plus, cached_network_image, flutter_local_notifications)
- [ ] **T-106** Set up project folder structure: config/, models/, services/, providers/, screens/, widgets/
- [ ] **T-107** Create `api_config.dart` — base URL (dev: localhost:8000, prod: domain)
- [ ] **T-108** Create `app_theme.dart` — brand color #588161, text styles, button styles, input decoration
- [ ] **T-109** Create `routes.dart` — named routes for all screens

### 4.2 Models (11 models)
- [ ] **T-110** Model: `User` (fromJson/toJson)
- [ ] **T-111** Model: `PhotographerProfile` (fromJson/toJson)
- [ ] **T-112** Model: `Category` (fromJson/toJson)
- [ ] **T-113** Model: `CameraKit` (fromJson/toJson)
- [ ] **T-114** Model: `WorkCity` (fromJson/toJson)
- [ ] **T-115** Model: `HireRequest` (fromJson/toJson)
- [ ] **T-116** Model: `AppNotification` (fromJson/toJson)
- [ ] **T-117** Model: `Country`, `StateModel`, `City` (fromJson/toJson)
- [ ] **T-118** Model: `Device` (fromJson/toJson)

### 4.3 Services
- [ ] **T-119** `ApiService` — Dio HTTP client with base URL, auth interceptor (attach Bearer token), error handling, response parsing
- [ ] **T-120** `AuthService` — register, login, logout, store/retrieve token (flutter_secure_storage), check auth state
- [ ] **T-121** `NotificationService` — Firebase messaging init, FCM token retrieval, foreground/background notification handling, send token to API

### 4.4 Providers (State Management)
- [ ] **T-122** `AuthProvider` — auth state, user data, login/logout/register methods
- [ ] **T-123** `ProfileProvider` — profile data, categories, camera kit, work cities, update methods
- [ ] **T-124** `HireRequestProvider` — requests list, filter by status, accept/decline methods
- [ ] **T-125** `NotificationProvider` — notifications list, unread count, mark read

### 4.5 Reusable Widgets
- [ ] **T-126** `LocationPicker` widget — reusable Country → State → City cascading dropdown (used in register, edit profile, work cities)
- [ ] **T-127** Category chips widget — multi-select from 16 categories with charge input per category
- [ ] **T-128** Camera kit list widget — add/remove text entries
- [ ] **T-129** Status badge widget — Pending (yellow), Accepted (green), Declined (red), Invalidated (grey)

### 4.6 Screens
- [ ] **T-130** Splash screen — logo, auto-login check (read stored token → validate → navigate)
- [ ] **T-131** Register screen — 6 fields with location picker + password → call register API → navigate to dashboard
- [ ] **T-132** Login screen — email + password → call login API → navigate to dashboard
- [ ] **T-133** Home / Dashboard screen — profile summary card, pending requests count, recent notifications
- [ ] **T-134** My Profile screen — display all profile data, categories with charges, camera kit, work cities, social links, portfolio link, profile completion %
- [ ] **T-135** Edit Profile screen — edit bio, experience, default charge, category charges, camera kit, work cities, instagram, facebook, portfolio
- [ ] **T-136** Hire Requests screen — list with status filter tabs (All / Pending / Accepted / Declined), pull-to-refresh
- [ ] **T-137** Hire Request Detail screen — full details + Accept / Decline buttons (only for pending)
- [ ] **T-138** Notifications screen — list with read/unread indicator, tap to mark read

### 4.7 Push Notifications (FCM)
- [ ] **T-139** Firebase project setup — add android + ios + web apps
- [ ] **T-140** Configure `firebase_messaging` in Flutter — request permission, get token, handle foreground/background
- [ ] **T-141** Send FCM token to API on login/register (POST /api/v1/fcm-token)
- [ ] **T-142** Handle notification tap — navigate to hire request detail or notifications screen

### 4.8 Final Integration & Polish
- [ ] **T-143** Deep link handling — notification tap opens correct screen
- [ ] **T-144** Error handling — network errors, session expired (401), server errors
- [ ] **T-145** Loading states — shimmer/skeleton for all list screens
- [ ] **T-146** Empty states — "No requests yet", "No notifications", "Complete your profile"
- [ ] **T-147** Pull-to-refresh on all list screens
- [ ] **T-148** App icon & splash branding (#588161)
- [ ] **T-149** Build & test on Android, iOS, Web

---

## Summary

| Phase | Tasks | Description |
|-------|-------|-------------|
| **Phase 1** | T-001 → T-070 | Laravel Backend API (70 tasks) |
| **Phase 2** | T-071 → T-092 | Laravel Admin Panel (22 tasks) |
| **Phase 3** | T-093 → T-103 | Flutter App Design (11 tasks) |
| **Phase 4** | T-104 → T-149 | Flutter App Development (46 tasks) |
| **Total** | **149 tasks** | |

---

## Execution Order

```
Phase 1 (API) ──────────────────────────────────────────────►
    1.1 Setup → 1.2 Migrations → 1.3 Seeders → 1.4 Models
    → 1.5 Auth API → 1.6 Location API → 1.7-1.12 Feature APIs
    → 1.13 FCM → 1.14 Test

Phase 2 (Admin) ────────────────────────────────────────────►
    2.1 Layout → 2.2 Dashboard → 2.3-2.7 CRUD Pages → 2.8 Routes

Phase 3 (Design) ───────────────────────────────────────────►
    3.1 Theme → 3.2-3.11 Screen Designs

Phase 4 (Flutter Dev) ──────────────────────────────────────►
    4.1 Setup → 4.2 Models → 4.3 Services → 4.4 Providers
    → 4.5 Widgets → 4.6 Screens → 4.7 FCM → 4.8 Polish
```
