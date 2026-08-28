# Planet Hosts Reseller Panel — Master Spec

> Single source of truth for the Reseller System build. Created 2026-08-28.
> Principle: **do NOT duplicate/copy the master admin panel or create a second disconnected billing DB.**
> The reseller layer sits between Planet Hosts and the reseller's customers and **reuses the existing Planet Hosts billing architecture.**

## Architecture

```
                PLANET HOSTS
             MASTER ADMIN PANEL
                     │
          ┌──────────┴──────────┐
          │                     │
   Hosting/Billing        Server/Nodes
          │                     │
          └──────────┬──────────┘
                     │
              RESELLER SYSTEM
                     │
    ┌────────────────┼────────────────┐
    │                │                │
Reseller A       Reseller B       Reseller C
    │                │                │
Customers        Customers        Customers
    │                │                │
 Services         Services         Services
```

Layering (3 distinct levels, permissions never mixed):
`Planet Hosts Admin → Reseller Management → Reseller Panel → Reseller Customers → Customer Panel`

## 1. Dashboard
Revenue · Customers · Active services · New orders · Pending orders · Unpaid invoices ·
Open tickets · Resource usage · Recent activity · Notifications

## 2. Customers
Add / Edit / Delete / Suspend customer · customer login · services · orders ·
invoices · payments · tickets · notes · login history

## 3. Products & Packages
Reseller receives products/packages from Planet Hosts; creates own retail packages.
Chain: `Planet Hosts Package → Reseller Cost → Reseller Retail Price → Customer`
Types: Hosting, VPS, Dedicated, Email, Domains, Databases, SSL, Game servers,
Radio/streaming, Custom products

## 4. Billing
Orders · Invoices · Payments · Refunds · Credits · Recurring billing · Renewals ·
Failed payments · Payment history · Coupons · Taxes · Discounts
Integration: reuse existing Planet Hosts billing. NO second billing database.
Flow: `Customer → Reseller Order → Payment Confirmed → Planet Hosts Billing →
Provisioning → Node/Server → Service Created → Welcome Email`

## 5. Provisioning
Reseller should NOT need SSH/root. Provisioning runs through Planet Hosts backend.

## 6. White Label
Company name · Logo · Favicon · Colors · Website URL · Support email · Billing email ·
Contact info · Terms · Privacy policy · Login page branding · Customer panel branding · Email branding

## 7. Reseller Staff
Owner → Manager / Support / Billing / Technician, with granular permissions.

## 8. Resources (Planet Hosts controls limits; reseller cannot exceed)
Customers 500 · Hosting 500 · Storage 2TB · Bandwidth 20TB · Databases 2000 · Domains 1000 ·
VPS 25 · Game Servers 50 · Radio Stations 100

## 9. Support (two-level escalation)
`Customer → Reseller Support → Planet Hosts Support`

## 10. Security
2FA · Login protection · Session management · IP/device history · API keys ·
API permissions · Rate limiting · Audit logs · Failed-login tracking · Password reset ·
Email verification · Staff permissions · Account suspension

## 11. API (build in from day one)
Customers · Products · Orders · Invoices · Payments · Services · Domains · Tickets ·
Provisioning · Suspension · Unsuspension

## 12. Audit Log
User · Action · Date/time · IP · Resource · before/after values.
Example: "Reseller John created customer Bob / created hosting account bob123 /
changed package / issued invoice / suspended service / logged in"

## Planet Hosts Master Admin — Reseller Management section
Admin → Resellers:
All Resellers · Add Reseller · Reseller Details · Customers · Products · Resources ·
Pricing · Billing · Commissions/Margins · Branding · Staff · API · Support · Activity · Audit Log

## Current-state assessment (2026-08-28)
- `admin/Controllers/ResellerController.php` + `admin/Views/reseller/*` exist (basic CRUD:
  list/create/edit, assigns `hosting_users.reseller_id`, `feature_list_id`).
- `resellers` table: id, admin_id, company_name, contact_name, email, phone, website,
  theme_settings, is_active, created_at, updated_at.
  Missing: resources/limits, margins, branding fields, feature_list_id/features (used by controller but NOT in table → BUG), staff, api key link, audit.
- `user/Controllers/ResellerPortalController.php` + `user/Views/reseller/*` exist (dashboard,
  clients, packages, billing, branding, support) — thin, uses `resellers` + `billing_services`/`hosting_users`.
- `public/portal_reseller.php` exists (legacy portal).
- No reseller-specific: commissions/margins, resource enforcement, staff/permissions, audit log,
  API keys, provisioning passthrough, coupon/tax/refund breadth, white-label customer panel.
- Existing tables to reuse: `hosting_users` (reseller_id), `resellers`, `billing_products`, `billing_services`,
  `billing_orders`, `invoices`, `payments`, `api_keys`, `studio_audit_logs` (audit reference).

## Build phases
1. **DB/permissions first** — extend `resellers` (resources, margins, branding, feature_list_id/features),
   add `reseller_staff`, `reseller_audit_logs`, `reseller_api_keys`. Migration file, no breakage.
2. **Planet Hosts master admin → Reseller Management** — list/add/details/customers/products/resources/
   pricing/billing/margins/branding/staff/API/support/activity/audit.
3. **Reseller Panel** — dashboard, customers, products, billing, provisioning, white-label, staff, API.
4. **Customer + billing/provisioning integration** — reuse existing Planet Hosts billing; welcome email; suspend.