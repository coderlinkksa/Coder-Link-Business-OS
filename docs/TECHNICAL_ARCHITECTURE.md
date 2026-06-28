# Technical Architecture Overview

This document defines the high-level technical architecture for Coder Link Business OS.
It is a planning document only. No code, database tables, or implementation details
are included. All decisions here must be consistent with the existing business documents
in this repository.

**Status:** Draft

---

## 1. System Purpose

Coder Link Business OS is the internal operating system that powers how Coder Link
runs its business. Its first and primary purpose is to give the company a repeatable,
automated system for attracting leads, managing the sales pipeline, delivering projects,
and retaining customers through recurring services.

The system is built for a small, remote-first team operating in the Saudi market.
It must be operable by non-developers, reduce manual work progressively through
automation, and scale as the business grows — without requiring a full rebuild.

The first production priority is the **Sales Engine and CRM**. Marketing automation
is a later phase.

---

## 2. Main Modules

| # | Module | Primary Purpose |
|---|--------|-----------------|
| 1 | CRM | Manage leads, contacts, companies, and the full sales pipeline |
| 2 | Sales | Track opportunities, proposals, and closed deals |
| 3 | Proposals | Generate, store, and track formal offers sent to customers |
| 4 | Project Management | Track delivery of sold projects from start to handover |
| 5 | Subscriptions & Renewals | Manage maintenance contracts, hosting, and renewal cycles |
| 6 | Invoicing | Issue and track payment of invoices |
| 7 | AI Automation | Automate repetitive tasks and augment team decisions |
| 8 | Notifications | Alert the team to important events and deadlines |
| 9 | Integrations | Connect to Gmail, Google Drive, and external services |

---

## 3. Recommended Technology Stack

| Role | Technology | Reason |
|------|------------|--------|
| Main business application | **Laravel (PHP)** | Mature, well-documented, suited to a small team, handles business logic and data ownership cleanly |
| Automation engine | **n8n** (self-hosted) | Visual workflow builder, easy to maintain without deep code knowledge, large connector ecosystem |
| Database | **PostgreSQL** | Reliable relational database for structured business data |
| File storage & document management | **Google Drive** | Existing tool, team-friendly, no additional infrastructure |
| Email communication | **Gmail / Google Workspace** | Existing tool, team-familiar, integrates with n8n |
| Source control & documentation | **GitHub** | Single source of truth for all code, workflows, and documents |
| Background jobs & queues | **Laravel Queues + Redis** | Handles asynchronous tasks within the Laravel application |
| Local development | **Docker** | Reproducible environment for the full stack |

---

## 4. Laravel Application Role

Laravel is the **core business application**. It is responsible for:

- Owning all business data: leads, contacts, companies, opportunities, proposals, projects, contracts, invoices, subscriptions, employees, activities, tasks, and follow-ups.
- Enforcing all business rules: validation, status transitions, access control, and data integrity.
- Providing the internal web interface used by the Coder Link team (admin panel, dashboards, forms).
- Exposing internal APIs consumed by n8n for automation triggers and data updates.
- Sending structured events that n8n workflows can react to.
- Managing user authentication, roles, and permissions.

Laravel is the **system of record**. n8n does not own data — it reads from and writes to Laravel.

---

## 5. n8n Automation Role

n8n is the **automation and integration layer**. It is responsible for:

- Listening to events from Laravel and triggering follow-up actions.
- Sending notifications to the team via Gmail or other channels.
- Creating and organising folders and files in Google Drive.
- Scheduling recurring tasks: renewal reminders, follow-up alerts, overdue invoice notifications.
- Running scheduled data checks and generating summary reports.
- Connecting to third-party services where a native Laravel integration is not justified.
- Handling AI automation flows: lead scoring, content generation, message drafting.

n8n does **not** own business data. All durable state lives in Laravel. n8n workflows are stateless — they read context, perform an action, and write the result back.

n8n workflow files are version-controlled in GitHub and are never edited directly in production without a corresponding commit.

---

## 6. GitHub Role

GitHub is the **single source of truth** for everything that can be version-controlled.

GitHub is responsible for:

- Storing all application code (Laravel).
- Storing all n8n workflow definitions (exported as files).
- Storing all project documentation including this architecture document.
- Storing all specification files.
- Providing a change history and review process for every meaningful change to the system.
- Enforcing that no change to production happens without a traceable commit.

No secrets, credentials, or environment-specific configuration files are stored in GitHub.

---

## 7. Google Drive Role

Google Drive is the **file and document management layer** for business files that are
produced for and shared with customers.

Google Drive is responsible for:

- Storing proposal documents.
- Storing signed contracts.
- Storing project deliverables.
- Organising per-customer folders automatically (triggered by n8n on company creation).
- Storing media assets produced for marketing or delivery.
- Providing a shareable, familiar interface for the team and for customers.

Google Drive does **not** replace the database. Metadata about files (name, location, status, link) is recorded in Laravel. The file content lives in Drive.

---

## 8. Gmail Role

Gmail is the **primary communication channel** between Coder Link and its customers,
and between n8n and the internal team.

Gmail is responsible for:

- Sending proposal notifications and follow-up reminders to customers.
- Delivering internal team alerts triggered by n8n workflows.
- Receiving inbound enquiries that are captured and converted to leads.
- Providing an audit trail of customer communications accessible to the team.

Gmail is not a data store. Important communication events are logged as Activities in Laravel.

---

## 9. Database Role

PostgreSQL is the **structured data store** for all business records managed by the Laravel application.

The database is responsible for:

- Persisting all CRM, sales, project, subscription, and invoicing data.
- Enforcing relational integrity between business entities.
- Supporting reporting and pipeline visibility queries.
- Storing user accounts, roles, and permissions.

The database is accessed **only through the Laravel application**. n8n and other tools interact with data through Laravel's internal APIs, not by connecting to the database directly.

Database schema changes are managed through versioned Laravel migrations, reviewed and committed to GitHub before being applied.

---

## 10. CRM Module

The CRM module is the foundation of the business system and the **first priority for production**.

Responsibilities:
- Capture and store every inbound lead from all sources.
- Track the full lifecycle of a lead from first contact to customer.
- Manage company records and all associated contacts.
- Record every activity, task, follow-up, and communication.
- Assign leads and accounts to employees with clear ownership.
- Provide pipeline visibility across all sales stages as defined in the Sales Pipeline document.

The CRM module consumes the entity definitions from `docs/CRM_DOMAIN_MODEL.md` and
the stage definitions from `docs/SALES_PIPELINE.md`.

---

## 11. Sales Module

The Sales module manages the commercial progression of an opportunity from qualification to closed deal.

Responsibilities:
- Track open opportunities, their stage, value, and probability.
- Link opportunities to companies, contacts, and assigned employees.
- Record all stage transitions with timestamps and notes.
- Trigger the Proposals module when an opportunity reaches Solution Design.
- Mark opportunities as Won or Lost with a recorded reason.
- Feed closed-Won opportunities into the Project Management module.

---

## 12. Proposal Module

The Proposal module manages the creation, tracking, and acceptance of formal offers.

Responsibilities:
- Generate structured proposal documents based on a defined template.
- Store the proposal in Google Drive inside the customer's folder.
- Record the proposal in Laravel with its status and linked opportunity.
- Notify the team when a proposal is sent, viewed, accepted, or rejected.
- Support multiple revisions with version tracking.
- Trigger the contract and project creation flow when a proposal is accepted.

---

## 13. Project Management Module

The Project Management module tracks all work that is delivered to customers after a deal is won.

Responsibilities:
- Create a project record linked to the won opportunity and signed contract.
- Track project status, milestones, and completion.
- Assign tasks to employees with due dates.
- Automatically create the customer's Google Drive project folder via n8n.
- Record the handover date and trigger the Support or Maintenance flow upon delivery.
- Log all project activities for visibility and audit.

---

## 14. Subscription & Renewal Module

The Subscription and Renewal module manages all recurring revenue relationships.

Responsibilities:
- Track every active maintenance contract, hosting subscription, and service renewal.
- Store the start date, renewal date, value, and status of each subscription.
- Trigger automated renewal reminder workflows in n8n at defined intervals before expiry.
- Generate renewal invoices at the correct time.
- Record renewal outcomes: renewed, cancelled, or upgraded.
- Support upsell identification by surfacing upcoming renewals with upsell potential.

---

## 15. AI Automation Module

The AI Automation module augments the team's work by handling repetitive, rule-based,
or data-heavy tasks that benefit from machine intelligence.

Responsibilities:
- Draft initial responses to inbound leads based on the service they enquired about.
- Score or classify incoming leads based on defined criteria.
- Generate first-draft proposal content based on requirements input.
- Summarise activity history for a contact or company before a sales call.
- Send automated WhatsApp follow-up messages through n8n at defined intervals.
- Assist with content generation for marketing as a later phase.

All AI automation flows run through n8n. AI outputs are reviewed by a team member
before being sent to customers unless a specific flow has been approved for full automation.

---

## 16. Integration Boundaries

| Integration | Direction | Managed By | Purpose |
|-------------|-----------|------------|---------|
| Laravel → PostgreSQL | Read / Write | Laravel | All business data persistence |
| Laravel → n8n | Event push (webhook) | Laravel | Trigger automation on business events |
| n8n → Laravel | API call | n8n | Write results back after automation |
| n8n → Gmail | Send / Read | n8n | Notifications, outbound emails, lead capture |
| n8n → Google Drive | Create / Upload / Organise | n8n | Customer folders, proposals, contracts |
| n8n → AI Provider | API call | n8n | Text generation, classification, summarisation |
| n8n → WhatsApp | Send | n8n | Customer follow-up and notifications |

**Boundary rules:**
- Laravel does not call external APIs directly (except for well-justified exceptions).
- n8n does not access the database directly.
- No credentials are stored in workflow files or in the GitHub repository.
- All integrations are documented and version-controlled.

---

## 17. Security Principles

1. **No secrets in source control.** All credentials live in environment variables or a secrets manager. `.env` files are never committed.
2. **Role-based access control.** Every internal user has a defined role. Permissions are enforced in Laravel and are not assumed to be enforced by the UI alone.
3. **Least privilege.** Each integration uses only the permissions it requires. n8n credentials are scoped to the minimum necessary access.
4. **Audit trail.** All significant actions — login, status changes, data edits, proposal sends — are logged with a timestamp and the responsible user.
5. **Environment separation.** Development, staging, and production are strictly separated. No production data is used in development.
6. **Data belongs to the business.** All customer data is stored in systems owned or controlled by Coder Link, not in third-party platforms as the primary record.

---

## 18. Future SaaS Readiness

While the first version of Coder Link Business OS is built as an internal tool for one company, the architecture is designed to avoid decisions that would block a future multi-tenant SaaS offering.

Practices that preserve SaaS readiness:
- Every business record is associated with an owner entity, making tenant-scoping a future addition rather than a redesign.
- The module boundaries and clear separation of concerns allow individual modules to be packaged and licensed independently.
- n8n workflows are treated as configuration, not custom code, making them customisable per tenant in the future.
- Google Drive organisation is structured by customer from day one, not by employee or project type, making per-tenant file isolation natural.

No multi-tenant features will be built until the single-tenant system is proven in production.

---

## 19. What Must NOT Be Built Yet

The following are explicitly out of scope for the first production version. They will be specified in future orders.

- Marketing automation (social media scheduling, SEO tooling, campaign management).
- Public-facing customer portal.
- Multi-tenant / SaaS infrastructure.
- Advanced analytics or business intelligence dashboards.
- AI that acts fully autonomously without human review.
- Custom payment gateway integration.
- Mobile application.
- API platform for external developers.

The first production release must prove the Sales Engine and CRM work reliably before any of these are considered.
