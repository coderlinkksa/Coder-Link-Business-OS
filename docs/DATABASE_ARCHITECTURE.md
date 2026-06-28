# Database Architecture

This document defines the high-level PostgreSQL database architecture for Coder Link
Business OS. It covers data ownership, isolation strategy, lifecycle patterns, and
future readiness. No SQL, migrations, column definitions, or implementation code
are included.

**Status:** Draft

---

## 1. Database Purpose

The PostgreSQL database is the single, authoritative system of record for all business
data in Coder Link Business OS. Its role is defined in ADR-003 and the Technical
Architecture document.

The database is responsible for:
- Persisting all business entities owned by the Laravel application.
- Enforcing relational integrity between those entities.
- Supporting pipeline visibility, reporting, and audit queries.
- Storing user accounts, roles, and permissions.

The database is accessed exclusively through the Laravel application. No external
tool — including n8n — connects to the database directly. All data access for
automation purposes goes through Laravel's internal APIs.

---

## 2. Data Ownership by Module

Each module defined in the System Modules document owns a distinct area of the
database. No module reads or writes directly into another module's data area.
Cross-module data access happens through Laravel service boundaries, not through
direct cross-area queries.

| Module | Owns |
|--------|------|
| Identity & Access | Users, roles, permissions, sessions, authentication events |
| Company Management | Companies, contact persons, account status |
| CRM | Leads, lead stages, activities, follow-ups, tasks |
| Sales | Opportunities, opportunity stages, deal outcomes |
| Proposal Management | Proposals, proposal versions, proposal status |
| Contracts | Contracts, contract status, contract terms |
| Project Management | Projects, milestones, project tasks, delivery records |
| Billing & Subscriptions | Invoices, invoice status, maintenance contracts, hosting subscriptions, renewal records |
| Support & Maintenance | Support requests, maintenance schedules, resolution records |
| Notifications | Notification log, notification preferences |
| Integrations | Integration event log, external reference records |
| AI Services | AI generation log, classification results |
| Administration | System configuration, audit log |

---

## 3. Core Business Data Areas

The database is organised into the following high-level data areas, each grouping
related entities by business concern.

**Identity Area**
Covers all user accounts, defined roles, permission sets, and the history of
authentication events. This is the foundational area that all others depend on.

**Account Area**
Covers the master records of companies and their contact persons. This is the
central reference point for every other business data area. All other areas
link their records back to a company.

**Pipeline Area**
Covers the progression of business from first contact to closed deal: leads,
opportunities, and their associated stage history. This area represents the
commercial engine of the system.

**Commercial Area**
Covers the formal commitments made to customers: proposals, contracts, and
the status tracking of each. These records are the legal and financial backbone
of every customer relationship.

**Delivery Area**
Covers the work done to fulfil a contract: projects, milestones, tasks, and
the handover record. This area bridges the commercial commitment to the
operational outcome.

**Revenue Area**
Covers all financial records: invoices, maintenance contracts, hosting
subscriptions, and renewal cycles. This area is the source of truth for
all money owed and received.

**Retention Area**
Covers post-delivery customer activity: support requests, maintenance
schedules, and resolution history. This area tracks the quality of ongoing
service.

**System Area**
Covers cross-cutting system concerns: the audit log, notification log,
integration event log, AI generation log, and system configuration.
This area is never written to by business modules directly — it receives
structured records from a dedicated logging service boundary.

---

## 4. Tenant Isolation Strategy

In the first production version, Coder Link Business OS is a single-tenant
internal system. There is one company using the system: Coder Link itself.
Its customers are the companies managed within the CRM.

However, the database is designed from day one to support future multi-tenancy
without a structural rebuild. The strategy is:

**Row-level isolation with an owner reference.**
Every business record that would need to be isolated per tenant in a future SaaS
version carries an owner reference field. In the single-tenant version, this field
has a single value. In a future multi-tenant version, it becomes the partition key
for all queries.

This approach means:
- No structural schema changes are required to introduce multi-tenancy later.
- Queries that filter by owner reference are already correct — they just need
  the tenant value to vary.
- Security policies that enforce tenant-scoping can be layered on without
  rewriting application logic.

The owner reference concept is introduced in the data model from the first schema
migration and is treated as mandatory on every relevant data area.

---

## 5. Shared vs Tenant-Owned Data

**Shared data** is data that belongs to the platform itself and would be common
across all tenants in a future SaaS version.

Examples of shared data:
- Role definitions and permission structures.
- System configuration defaults.
- Notification template definitions.
- Service catalog reference data.

**Tenant-owned data** is data that belongs to a specific tenant and must be
strictly isolated.

Examples of tenant-owned data:
- All company and contact records.
- All pipeline records: leads, opportunities.
- All commercial records: proposals, contracts.
- All delivery records: projects, tasks.
- All revenue records: invoices, subscriptions.
- All retention records: support requests, maintenance logs.
- All AI generation and integration logs related to tenant activity.

In the single-tenant version, this distinction is a design classification.
In a future multi-tenant version, it becomes the enforcement boundary.

---

## 6. Audit Log Strategy

The audit log is an append-only record of every significant action taken in the system.
It is write-once: records are never updated or deleted.

**What is logged:**
- Record creation, modification, and status changes for all business entities.
- User authentication events: login, logout, failed attempts.
- Permission changes and role assignments.
- Integration events: external API calls made, files created in Google Drive.
- AI generation requests and outcomes.
- System configuration changes.

**What each log entry records:**
- The action performed.
- The record affected (area and reference identifier).
- The user who performed the action.
- The timestamp.
- The previous state and the new state where applicable.

**Retention:**
Audit log records are retained for a minimum defined period and are never purged
without explicit administrative action. They are not subject to soft delete.

**Access:**
The audit log is readable only by users with the Administrator role. It is not
exposed to standard users through normal application screens.

---

## 7. Soft Delete Strategy

Business records in Coder Link Business OS are soft-deleted, not hard-deleted.
When a user removes a record, it is marked as deleted and hidden from normal
views but remains in the database.

**Why soft delete:**
- Preserves historical relationships: a deleted company may still be referenced
  by closed invoices or signed contracts.
- Supports audit and compliance requirements.
- Allows accidental deletions to be recovered by an Administrator.
- Prevents referential integrity violations when a referenced record is removed.

**What can be soft-deleted:**
All business entities: companies, contacts, leads, opportunities, proposals,
contracts, projects, tasks, invoices, subscriptions, support requests, employees.

**What cannot be soft-deleted:**
- Audit log entries (append-only, never deleted).
- Integration event log entries.
- AI generation log entries.
- Authentication events.

**Permanently deleted records:**
No record is permanently deleted from the system except through an explicit,
logged administrative action. Such actions are recorded in the audit log.

---

## 8. Status & Lifecycle Fields

Every business entity that has a defined lifecycle carries a structured status
field. Status values are defined per entity and correspond to the stages
documented in the CRM Domain Model and Sales Pipeline documents.

**Standard lifecycle fields present on all business entities:**
- Status — the current lifecycle state of the record.
- Created timestamp — when the record was first created.
- Updated timestamp — when the record was last modified.
- Created by — the user who created the record.
- Soft delete marker — whether the record has been logically removed.
- Soft delete timestamp — when the record was logically removed.

**Additional lifecycle fields on entities that change hands:**
- Assigned to — the Employee currently responsible for the record.
- Stage entered timestamp — when the current status was entered (for pipeline reporting).

Status transitions are enforced by the application layer, not by the database.
The database records the current status and its history; the Laravel application
enforces which transitions are permitted.

---

## 9. File Reference Strategy for Google Drive

The database does not store file content. All business documents — proposals,
contracts, deliverables, media — are stored in Google Drive as decided in ADR-005.

The database stores a reference record for every file managed in Google Drive.
Each reference record contains:
- The human-readable name of the file.
- The external identifier assigned by Google Drive.
- The shareable link to the file.
- The file type classification (proposal, contract, deliverable, media, other).
- The business record it belongs to (the linked company, proposal, project, etc.).
- The upload timestamp and the user who triggered the upload.
- The current status of the file (active, superseded, archived).

This approach means:
- The system can display, link to, and track all customer files without storing content.
- If a file is moved or renamed in Drive, the reference can be updated without
  affecting the business record it belongs to.
- File references are subject to soft delete and the audit log like all other records.

---

## 10. Integration Event Logging

Every interaction with an external service — Google Drive, Gmail, WhatsApp, an
AI provider — is logged as an integration event record.

Each integration event record contains:
- The external service involved.
- The type of action performed (create, send, upload, generate, etc.).
- The business record that triggered the action.
- The outcome: success, failure, or pending.
- The timestamp of the request and the timestamp of the response.
- A reference to the n8n workflow execution that performed the action.
- Any error detail if the action failed.

**Why this matters:**
- Provides a complete trace of every external action taken on behalf of a business record.
- Allows failed integrations to be identified, investigated, and retried.
- Supports the audit requirement to know what was sent, when, and to whom.
- Connects the Laravel audit trail to the n8n execution history.

Integration event log records are append-only and are not subject to soft delete.

---

## 11. Future SaaS Readiness

The database architecture avoids decisions that would require a full rebuild when
Coder Link Business OS is offered as a multi-tenant SaaS product.

**Design choices that preserve SaaS readiness:**

The owner reference field, present on all tenant-owned data from day one, becomes
the tenant partition key with no structural change.

Shared data (roles, templates, configuration) is already separated from tenant-owned
data, making it straightforward to define what is inherited from the platform and
what is customised per tenant.

The audit log, integration event log, and AI generation log are already designed
as append-only records scoped to business entities, making tenant-scoped log
queries a filter addition rather than a redesign.

The file reference strategy stores only references, not content. In a multi-tenant
version, each tenant's Drive folder is already isolated by the folder structure
created at company creation time.

**What is deliberately deferred:**
- Schema-per-tenant isolation (a more aggressive isolation model for enterprise tenants).
- Row-level security policies enforced at the database layer.
- Cross-tenant analytics and platform-level reporting.

These will be designed when the single-tenant system is proven in production and
multi-tenant requirements are formally specified.

---

## 12. What Must NOT Be Designed Yet

The following data areas are explicitly out of scope for the first production version.
They will be specified in future orders when the corresponding modules are ready to build.

- Marketing data: campaigns, content items, schedules, social media posts.
- Analytics and reporting data warehouse: aggregated performance metrics, funnel analysis.
- Public customer portal data: customer-facing sessions, self-service records.
- Payment gateway integration records.
- Multi-tenant platform-level data: tenant registry, tenant billing, platform audit.
- Mobile application session and device data.

The database must not be designed to accommodate these areas until their business
requirements and module specifications are formally provided. Pre-designing
unspecified areas creates schema debt that constrains future decisions.
