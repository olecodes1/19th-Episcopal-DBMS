# 19th Episcopal District (AME Church) Management System

## Executive Summary

The **19th Episcopal District Management System** (`AdminDash`) is a comprehensive web administration, membership tracking, event analytics, and digital archive platform built specifically for the **19th Episcopal District of the African Methodist Episcopal (AME) Church** in South Africa.

The platform provides centralized management across all levels of the church hierarchy—from local churches and geographic areas up to annual conferences and district leadership.

---

## 1. AME Church Organizational Structure

The AME Church operates as a connectional, hierarchical institution. Data flows upward through five primary operational levels:

```
[ Local Church / Circuit ] ──> [ Area ] ──> [ Annual Conference ] ──> [ Episcopal District (19th) ] ──> [ Connectional ]
```

### Hierarchical Breakdown

1. **Episcopal District Level**: **19th Episcopal District** (South Africa), led by the Presiding Bishop.
2. **Annual Conference Level**: 5 Annual Conferences within the 19th District:
   - **Mangena Maake Mokone Memorial Conference** (MM / Mokone Conference)
   - **Orangia Conference**
   - **Natal Conference**
   - **West Conference**
   - **East Conference**
3. **Area Level**: Geographic subdivisions within each Annual Conference (45 areas total across the 5 conferences).
4. **Local Church / Circuit Level**: Individual local congregations (196 local churches registered).
5. **Auxiliaries & Departments**:
   - **Young People's Department (YPD)** with four age components:
     - **Mother Sunbeams (MB)**: Ages 2–6
     - **Allen Stars (AS)**: Ages 7–12
     - **Youth Auxiliary (Y)**: Ages 13–17
     - **Young Adults (YA)**: Ages 18–26

---

## 2. System Architecture & Tech Stack

- **Backend**: Native PHP 8+ using PDO (`db.php`)
- **Database**: MySQL / MariaDB (`19edypd_db`)
- **Frontend**: Responsive HTML5, Vanilla CSS (`assets/`), and JavaScript
- **Modular Views**: Dynamic view rendering routed through `AdminDash/index.php`
- **Authentication**: Session-based admin login (`login.php`) with role-aware users table (`users`)
- **Public Surface**: `public_website/` read-only routes for members/visitors (home, events, media, stories)
- **CSV Data Exporter**: Active export engine (`actions/export.php`) for exporting filtered member rosters, churches, areas, and statistical reports.
- **Import Functionality Deprecation**: The CSV import features (`import_members.php`, `import_review_queue.php`, `import_jobs.php`) have been deprecated and completely removed from the codebase.

---

## 3. Database Schema Overview (`19edypd_db`)

The database consists of normalized tables configured to maintain relational integrity and auditability.

| Table Name | Description & Role | Key Fields |
|---|---|---|
| `episcopal_districts` | District root record (Seed: ID 19 = "19th Episcopal District") | `district_id`, `district_name` |
| `conferences` | The 5 Annual Conferences | `conference_id`, `district_id`, `conference_name`, `conference_president`, `conference_director` |
| `areas` | 45 Geographic Areas linked to Conferences | `area_id`, `district_id`, `conference_id`, `area_name`, `area_president_name`, `area_director_name` |
| `churches` | 196 Local Churches linked to Areas and Conferences | `church_id`, `district_id`, `conference_id`, `area_id`, `local_church_name`, `status` |
| `members` | Master membership registry (~3,830 records) | `member_id`, `member_no`, `name`, `surname_name`, `dob`, `gender`, `component`, `joined_ypd`, `robbed`, `year_robbed`, `full_member_of_church`, `eligible_to_vote_conference`, `eligible_to_vote_episcopal`, `occupational_status`, `contact` |
| `events` | District & Conference event schedules and attendance totals | `event_id`, `episcopal_district_id`, `conference_id`, `event_name`, `event_date`, `location`, `attendance_count` |
| `event_attendance_breakdowns` | Attendance breakdowns per church/area for events | `breakdown_id`, `event_id`, `conference_id`, `area_id`, `church_id`, `attendance_count` |
| `media_items` | Media library for photos, videos, and audio | `media_id`, `title`, `media_type`, `category`, `tags`, `event_tag`, `person_tag`, `media_year`, `file_path` |
| `legacy_leaders` | Historical registry of past Bishops, Directors, and Presidents | `leader_id`, `role_type`, `full_name`, `conference_name`, `start_year`, `end_year` |
| `milestones` | District historical timeline entries | `milestone_id`, `title`, `milestone_year`, `descriptions`, `achievements` |
| `story_pages` | CMS content articles and feature stories | `story_id`, `title`, `slug`, `story_year`, `status`, `cover_media_id`, `content` |
| `deleted_items` | Soft-delete recycle bin for restoring accidentally deleted records | `deleted_id`, `entity_table`, `entity_id`, `source_path`, `data_json`, `deleted_at`, `restored_at` |
| `users` | Admin authentication and role access records | `user_id`, `username`, `password_hash`, `role`, `conference_id`, `is_active`, `created_at` |

---

## 4. Master Data & Reference Breakdown

The reference database populated in `19edypd_db` contains:

| Conference Name | Areas Count | Local Churches Count | Members Count |
|---|:---:|:---:|:---:|
| **Natal Conference** | 4 Areas | 15 Churches | 343 members |
| **West Conference** | 10 Areas | 32 Churches | 868 members |
| **Orangia Conference** | 7 Areas | 29 Churches | 569 members |
| **Mangena Maake Mokone Memorial Conference** | 13 Areas | 61 Churches | 817 members |
| **East Conference** | 11 Areas | 59 Churches | 1,232 members |
| **TOTAL** | **45 Areas** | **196 Churches** | **3,829 Members** |

---

## 5. CSV Export Engine (`actions/export.php`)

The CSV Export module remains fully functional and accessible across views:
- **Members Roster Export**: Exports filtered member lists (`type=members`) with search, gender, YPD component, voting rights, and conference filters.
- **Churches Export**: Exports active church directory with conference and area details (`type=churches`).
- **Areas Export**: Exports area leadership and conference structure (`type=areas`).
- **Statistical Analytics Export**: Exports summary breakdowns for reports (`type=stats`).

---

## 6. Directory & File Structure Map

```
19thepiscopaldistrict/
├── Context.md                                        # Master Project Context & Documentation
├── ame-structure copy.md                             # AME Church Governance & Structural Reference
└── AdminDash/                                        # Primary Admin Dashboard Application
    ├── 19th Episcopal District Membership Stats 2026.csv # Master 2026 Membership CSV Dataset
    ├── 19thDistrict.png                              # District Branding & Visual Emblem
    ├── db.php                                        # Database Connection Handler (PDO)
    ├── index.php                                     # Main Dashboard Entry Point & Page Router
    ├── login.php                                     # Admin Login Page
    ├── public_website/                               # Public-facing read-only member/visitor site
    │
    ├── actions/                                      # Backend Business Logic & Processors
    │   ├── backup_bundle.php                         # System Data Export & Backup Tool
    │   ├── export.php                                # Active CSV Data Exporter (Members, Churches, Areas, Stats)
    │   ├── process_*.php                             # Entity Creation Handlers (Member, Church, Area, etc.)
    │   ├── update_*.php                              # Entity Update Handlers
    │   ├── delete_*.php                              # Entity Deletion Handlers (Routes to Recycle Bin)
    │   └── restore_deleted.php                       # Recycle Bin Restoration Processor
    │
    ├── assets/                                       # Front-End Assets (CSS Stylesheets, Icons)
    │
    ├── database/                                     # Database Schemas & Migrations
    │   ├── create_all_tables.sql                     # Complete Master Database Setup Script
    │   ├── rebuild_all_tables.sql                    # Database Rebuild & Wipe Script
    │   ├── recreate_members.sql                      # Members Table Schema Reset
    │   ├── schema_updates.sql                        # Incremental Database Patches
    │   └── seed_reference_data.sql                   # Generated Reference Data Seed Script
    │
    ├── forms/                                        # User Input & Editing Forms
    │   ├── add_member.php / edit_member.php          # Member Forms
    │   ├── add_church.php / edit_church.php          # Church Forms
    │   ├── add_area.php / edit_area.php              # Area Forms
    │   ├── add_conference.php / edit_conference.php  # Conference Forms
    │   ├── add_event.php / edit_event.php            # Event Forms
    │   └── add_media.php                             # Media Upload Form
    │
    ├── includes/                                     # Shared Layout Components & Core Helpers
    │   ├── header.php / footer.php                   # Global HTML Layout Wrappers
    │   ├── auth.php                                  # Session authentication helper/guard
    │   ├── feature_tables.php                        # Database Migration & Schema Compatibility Helpers
    │   ├── pagination.php                            # Table Pagination Component
    │   └── soft_delete.php                           # Global Soft-Delete Helper Function
    │
    └── views/                                        # Application Screen Views
        ├── members.php                               # Member Roster & Search Table (with Export CSV)
        ├── batch_members.php                         # Bulk member operations (batch updates/deletes)
        ├── conferences.php                           # Conferences Overview & Leadership
        ├── areas.php                                 # Geographic Areas Roster
        ├── churches.php / church_list.php            # Churches Directory (with Export CSV)
        ├── events.php / event_attendance.php         # Event Tracking & Attendance Analytics
        ├── media.php                                 # Media Gallery
        ├── story_pages.php / story_page.php          # District CMS & History Articles
        ├── statistical_reports.php                   # Demographic & Voting Analytics Reports (with Export CSV)
        ├── recycle_bin.php                           # Soft-Deleted Records Management
        └── search.php                                # Global Search Result Screen
```

---

## 7. Recent Implementation Milestones

- Added **batch member operations** (`views/batch_members.php`, `actions/bulk_members.php`) for bulk component/conference/area updates and soft-delete operations.
- Added **members filter enhancements** including empty/null component filtering and increased default pagination to 200 rows.
- Added optimized **Chart.js analytics visuals** to churches, conferences, and areas views.
- Hardened **media upload validation** (`actions/process_media.php`) with size limit, MIME/type consistency checks, and safer unique file naming.
- Added **users authentication model** and seeded superadmin through runtime migration logic and SQL migration script.
- Implemented **admin login/logout flow** and auth guard wiring.
- Introduced **public website surface** under `public_website/` with read-only home/events/media/stories routes.
