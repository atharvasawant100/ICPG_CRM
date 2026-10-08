# PRD: Lead Creation - Multi-Method Import System

**Document Version:** 1.0  
**Date:** 12 February 2026  
**Project:** ICPG Office CRM  
**Author:** Development Team  

---

## 1. Overview

### 1.1 Problem Statement
Currently, leads can only be added manually one at a time through the CRM form. This is inefficient when dealing with bulk data or leads coming from Meta (Facebook/Instagram) ad campaigns. Sales teams need faster, automated ways to import leads.

### 1.2 Objective
Provide **3 distinct methods** for creating leads in the CRM:
1. **Manual Entry** – Add a single lead via the existing form
2. **Bulk Import via XLSX** – Upload an Excel file to import multiple leads at once
3. **Meta Lead Integration** – Automatically pull leads from Facebook/Instagram Lead Ads

### 1.3 Success Metrics
- Reduce time to import 100+ leads from hours to minutes
- Zero manual data entry for Meta ad leads
- 95%+ successful bulk import rate (valid rows)

---

## 2. User Flow

### 2.1 Entry Point
When user clicks **"Add Lead"** from the Leads page, they see a **selection screen** with 3 options (cards/tabs):

```
┌─────────────────────────────────────────────────────────┐
│                     Add New Lead                        │
├──────────────┬──────────────┬───────────────────────────┤
│   📝         │   📊         │   📱                      │
│  Manual      │  Bulk Import │  Meta Leads               │
│  Entry       │  (XLSX)      │  (Facebook/Instagram)     │
│              │              │                           │
│ Add a single │ Upload Excel │ Connect your Meta ad      │
│ lead with    │ file to      │ account to auto-import    │
│ details      │ import many  │ leads from campaigns      │
│              │ leads        │                           │
└──────────────┴──────────────┴───────────────────────────┘
```

---

## 3. Feature Details

### 3.1 Option 1: Manual Entry (Existing)

**Status:** ✅ Already Implemented

**Current Fields:**
| Field    | Type     | Required |
|----------|----------|----------|
| Name     | Text     | Yes      |
| Email    | Email    | Yes      |
| Phone    | Text     | No       |
| Company  | Text     | No       |
| Source   | Dropdown | No       |
| Status   | Dropdown | No (default: "new") |

**Behavior:**
- User fills the form and submits
- Lead is created by the authenticated admin
- Redirects to leads index with success message

---

### 3.2 Option 2: Bulk Import via XLSX

**Status:** 🔨 To Be Built

#### 3.2.1 User Flow
1. User clicks "Bulk Import (XLSX)" card
2. Sees upload screen with:
   - Download template button (pre-formatted `.xlsx` file)
   - Drag & drop / file picker for upload
   - Instructions on how to fill the template
3. User uploads filled XLSX file
4. System validates and shows **preview** of data:
   - Total rows found
   - Valid rows (green)
   - Invalid rows with error reasons (red)
   - Duplicate detection (email already exists)
5. User confirms import
6. System creates leads and shows summary

#### 3.2.2 XLSX Template Columns
| Column      | Required | Validation                          |
|-------------|----------|-------------------------------------|
| Name        | Yes      | Max 255 chars                       |
| Email       | Yes      | Valid email format, unique in batch  |
| Phone       | No       | Max 255 chars                       |
| Company     | No       | Max 255 chars                       |
| Source      | No       | Must match: Website, Referral, Social Media, Email Campaign, Cold Call, Trade Show, Advertisement, Partner, Other |
| Status      | No       | Must match: new, contacted, qualified, converted, lost. Default: "new" |

#### 3.2.3 Technical Requirements

**Backend:**
- New route: `POST /leads/bulk-import`
- New controller method: `LeadController@bulkImport`
- Use `maatwebsite/excel` Laravel package for XLSX parsing
- Validation per row with error collection
- Transaction-based insert (all or nothing per batch)
- Max file size: 5MB
- Max rows per upload: 500

**Frontend:**
- New view: `resources/views/leads/bulk-import.blade.php`
- Drag & drop upload zone
- Progress indicator during processing
- Preview table with validation status per row
- Confirm/Cancel buttons

#### 3.2.4 Error Handling
| Error                    | Behavior                                         |
|--------------------------|--------------------------------------------------|
| Invalid file format      | Show error: "Please upload a valid .xlsx file"    |
| File too large           | Show error: "File size must be under 5MB"         |
| Missing required fields  | Highlight row, show which fields are missing      |
| Duplicate email in file  | Flag as warning, allow user to skip/include       |
| Duplicate email in DB    | Flag as warning, allow user to skip/update        |
| Empty file               | Show error: "File contains no data rows"          |

#### 3.2.5 Sample Template

```
| Name           | Email                  | Phone        | Company      | Source      | Status  |
|----------------|------------------------|--------------|--------------|-------------|---------|
| John Doe       | john@example.com       | 9876543210   | ABC Corp     | Website     | new     |
| Jane Smith     | jane@example.com       | 9876543211   | XYZ Ltd      | Referral    | new     |
```

---

### 3.3 Option 3: Meta Lead Integration (Facebook/Instagram)

**Status:** 🔨 To Be Built

#### 3.3.1 Overview
Connect the CRM with Meta (Facebook) Lead Ads API to automatically import leads generated from Facebook and Instagram ad campaigns.

#### 3.3.2 User Flow

**First-Time Setup (Admin Only):**
1. Admin navigates to **Settings → Meta Integration**
2. Clicks "Connect Meta Account"
3. Redirected to Facebook OAuth login
4. Grants permissions for Lead Ads access
5. Selects Facebook Page(s) to sync
6. Chooses sync settings:
   - Auto-sync interval (every 5 min / 15 min / 30 min / 1 hour)
   - Default lead status (new)
   - Default lead source ("Meta Ads")
   - Enable/disable auto-notifications
7. Saves configuration

**Ongoing Operation:**
- System automatically polls Meta API at configured interval
- New leads are created in CRM with source = "Meta Ads"
- Admin receives notification for each new batch
- Duplicate leads (by email) are skipped

#### 3.3.3 Data Mapping

| Meta Lead Field     | CRM Lead Field | Notes                              |
|---------------------|----------------|------------------------------------|
| full_name           | name           | Combined first + last name         |
| email               | email          | Primary identifier                 |
| phone_number        | phone          | With country code                  |
| company_name        | company        | If available                       |
| —                   | source         | Auto-set to "Meta Ads"             |
| —                   | status         | Auto-set to "new"                  |
| campaign_name       | notes          | Stored as first note on the lead   |
| ad_name             | notes          | Appended to campaign info          |
| created_time        | meta_lead_date | Custom field for original lead date|

#### 3.3.4 Technical Requirements

**Meta API Setup:**
- Meta App registration (developers.facebook.com)
- Required permissions: `pages_manage_ads`, `leads_retrieval`, `pages_read_engagement`
- OAuth 2.0 flow for authentication
- Webhook subscription for real-time updates (preferred over polling)
- Long-lived access token management (60-day refresh)

**Backend:**
- New migration: `meta_integrations` table
  ```
  - id
  - user_id (admin who connected)
  - page_id (Facebook page ID)
  - page_name
  - access_token (encrypted)
  - token_expires_at
  - sync_interval (minutes)
  - default_status
  - is_active (boolean)
  - last_synced_at
  - created_at
  - updated_at
  ```
- New migration: `meta_lead_logs` table
  ```
  - id
  - meta_integration_id
  - meta_lead_id (Facebook lead ID - for dedup)
  - lead_id (local CRM lead ID)
  - campaign_name
  - ad_name
  - form_name
  - raw_data (JSON)
  - status (imported / skipped / failed)
  - created_at
  ```
- New controller: `MetaIntegrationController`
- New service: `MetaLeadService` (API calls, data mapping)
- Laravel scheduled command: `leads:sync-meta` (runs at configured interval)
- Webhook endpoint: `POST /webhooks/meta/leads`

**Frontend:**
- New view: `resources/views/settings/meta-integration.blade.php`
- Connection status indicator (green/red)
- Sync history log
- Manual "Sync Now" button
- Disconnect option

#### 3.3.5 Meta App Configuration (Pre-requisites)

1. Create a Meta App at [developers.facebook.com](https://developers.facebook.com)
2. Add "Facebook Login" and "Leads Retrieval" products
3. Configure OAuth redirect URI: `https://yourdomain.com/auth/meta/callback`
4. Submit for App Review (required for production):
   - `pages_manage_ads`
   - `leads_retrieval`
   - `pages_read_engagement`
5. Get App ID and App Secret

#### 3.3.6 Webhook vs Polling

| Method    | Pros                           | Cons                              |
|-----------|--------------------------------|-----------------------------------|
| Webhook   | Real-time, no delay            | Requires HTTPS, public URL        |
| Polling   | Works on localhost, simpler    | Delay, API rate limits            |

**Recommendation:** Implement **polling first** (simpler), then add **webhook** support for production.

#### 3.3.7 Error Handling
| Error                        | Behavior                                      |
|------------------------------|-----------------------------------------------|
| Token expired                | Notify admin, pause sync, show reconnect prompt |
| API rate limit               | Retry with exponential backoff                 |
| Invalid/missing email        | Log as "skipped" in meta_lead_logs            |
| Duplicate lead               | Skip, log as "skipped (duplicate)"            |
| Meta API down                | Retry next interval, log error                |

---

## 4. Permissions & Access Control

| Feature              | Admin |
|----------------------|-------|
| Manual Entry         | Yes   |
| Bulk Import (XLSX)   | Yes   |
| Meta Integration     | Yes   |
| View imported leads  | Yes   |

The application is admin-only; employee role has been removed.

---

## 5. Implementation Priority & Phases

### Phase 1 (Week 1-2): Bulk XLSX Import
- Install `maatwebsite/excel` package
- Create upload UI with template download
- Build validation and preview
- Implement batch import with transaction
- Add error reporting

### Phase 2 (Week 3-4): Meta Integration - Basic
- Create Meta App and get credentials
- Build OAuth connection flow
- Implement polling-based lead sync
- Create sync settings page
- Add sync history/logs

### Phase 3 (Week 5): Meta Integration - Advanced
- Add webhook support for real-time sync
- Token auto-refresh mechanism
- Campaign/Ad level reporting
- Sync status dashboard

---

## 6. Database Changes

### New Tables:
1. `meta_integrations` – Store Meta connection details
2. `meta_lead_logs` – Track every lead imported from Meta

### Modified Tables:
- `leads` – Add optional `meta_lead_id` column (nullable, for dedup tracking)

---

## 7. Dependencies

| Package/Service            | Purpose                      | Status      |
|----------------------------|------------------------------|-------------|
| `maatwebsite/excel` ^3.1   | XLSX file parsing            | To install  |
| Meta Graph API v18.0+      | Lead Ads data retrieval      | To setup    |
| `laravel/socialite`        | OAuth flow (optional)        | To install  |
| SSL Certificate            | Required for Meta webhooks   | Check       |

---

## 8. UI/UX Mockup Description

### Lead Creation Selection Page (`/leads/create`)
- **3 cards** displayed in a row (desktop) / stacked (mobile)
- Each card has:
  - Icon (emoji or SVG)
  - Title
  - Short description
  - CTA button
- Cards have hover effect with subtle shadow
- Active/selected card highlighted with border color

### Bulk Import Page (`/leads/bulk-import`)
- Step 1: Download template + Upload file
- Step 2: Preview & validate (table view)
- Step 3: Confirm import → Success summary

### Meta Settings Page (`/settings/meta-integration`)
- Connection status card
- Connected pages list
- Sync settings form
- Recent sync logs table
- Manual sync button

---

## 9. Open Questions

1. **Bulk Import Limit:** Is 500 rows per upload sufficient, or do we need higher?
2. **Meta Campaigns:** Do we need to filter leads by specific campaign, or import all?
3. **Duplicate Handling:** Should duplicate emails update existing lead or skip entirely?
4. **Lead Source Tracking:** Should we add a `import_method` field (manual/xlsx/meta) for analytics?

---

## 10. Glossary

| Term           | Definition                                                |
|----------------|-----------------------------------------------------------|
| Meta Lead Ads  | Facebook/Instagram ad format that collects user info via in-app forms |
| XLSX           | Microsoft Excel file format                                |
| OAuth          | Authentication protocol used by Meta for third-party access |
| Webhook        | HTTP callback that delivers real-time notifications        |
| Polling        | Periodically checking an API for new data                  |
| Dedup          | De-duplication – preventing duplicate records              |
