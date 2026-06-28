# System Modules Architecture

This document defines the Coder Link Business OS as a set of independent, bounded
modules. Each module has a clear purpose, defined responsibilities, and explicit
boundaries. No implementation, database, or code details are included.

**Status:** Draft

---

## 1. Identity & Access

**Purpose**
Control who can access the system and what each user is permitted to do.

**Responsibilities**
- Manage user accounts and credentials.
- Define and enforce roles: Owner, Admin, Sales Representative, Account Manager, Technical Support, Viewer.
- Control access to modules, records, and actions based on role.
- Maintain a session and authentication state.
- Log all authentication events.

**Inputs**
- Login credentials from a user.
- Role assignment instructions from an Administrator.

**Outputs**
- Authenticated session for the requesting user.
- Permission grants or denials for any requested action.
- Authentication event log entries.

**Depends On**
- Nothing. This is the lowest-level module.

**Used By**
- Every other module in the system.

---

## 2. Company Management

**Purpose**
Maintain the master record of every business organisation that Coder Link deals with.

**Responsibilities**
- Store and manage Company records with all associated details.
- Store and manage Contact Person records linked to each Company.
- Track the company account status: Prospect, Active Customer, Inactive, Churned.
- Trigger the creation of the company's Google Drive folder when a new company is created.
- Provide company and contact data to all other modules that need it.

**Inputs**
- New company created from a qualified Lead (from CRM).
- Manual creation by an authorised user.
- Updates to company or contact details by an authorised user.

**Outputs**
- Company record available to all modules.
- Contact Person records available to all modules.
- Event: `company.created` — consumed by the Integrations module to create the Drive folder.

**Depends On**
- Identity & Access

**Used By**
- CRM
- Sales
- Proposal Management
- Contracts
- Project Management
- Billing & Subscriptions
- Support & Maintenance
- Notifications
- Integrations

---

## 3. CRM

**Purpose**
Capture every inbound lead and manage its full lifecycle from first contact to qualified opportunity or disqualification.

**Responsibilities**
- Receive and store every inbound lead from all sources.
- Track lead status through all defined pipeline stages.
- Assign leads to Sales Representatives.
- Record all activities, follow-ups, and tasks against a lead.
- Convert a qualified lead into a Company, Contact Person, and Opportunity.
- Prevent lead loss through follow-up tracking and overdue alerts.

**Inputs**
- Lead submission from a website form or manual entry.
- Status updates and activity notes from Sales Representatives.
- Follow-up completion events from the Notifications module.

**Outputs**
- Lead records available for pipeline visibility.
- Event: `lead.qualified` — triggers creation of Company, Contact, and Opportunity.
- Event: `lead.lost` — closes the lead with a recorded reason.
- Activity and follow-up records.
- Overdue follow-up alerts sent to the Notifications module.

**Depends On**
- Identity & Access
- Company Management (upon lead conversion)

**Used By**
- Sales
- Notifications
- AI Services

---

## 4. Sales

**Purpose**
Manage the commercial progression of every opportunity from qualification to a won or lost outcome.

**Responsibilities**
- Track open Opportunities with stage, value, probability, and assigned owner.
- Record all stage transitions with timestamps and notes.
- Provide pipeline visibility across all active deals.
- Trigger Proposal creation when an Opportunity reaches the Solution Design stage.
- Record Won or Lost outcomes with reasons.
- Hand off won Opportunities to Project Management and Contracts.

**Inputs**
- New Opportunity created from a converted Lead (from CRM).
- Stage updates and notes from Sales Representatives and Account Managers.
- Proposal acceptance or rejection events (from Proposal Management).

**Outputs**
- Opportunity records for pipeline reporting.
- Event: `opportunity.won` — triggers Contract and Project creation.
- Event: `opportunity.lost` — closes the deal with a reason recorded.
- Request to Proposal Management to create a proposal.

**Depends On**
- Identity & Access
- Company Management
- CRM

**Used By**
- Proposal Management
- Contracts
- Project Management
- Notifications
- AI Services
- Administration

---

## 5. Proposal Management

**Purpose**
Generate, track, and manage formal offers sent to customers.

**Responsibilities**
- Create proposal documents from defined templates.
- Store proposals in the customer's Google Drive folder via the Integrations module.
- Record each proposal in the system with its version, status, and linked opportunity.
- Track proposal status: Draft, Sent, Under Review, Accepted, Rejected, Expired.
- Support multiple revisions with version tracking.
- Notify the team on key proposal events.

**Inputs**
- Request from Sales to create a proposal for an Opportunity.
- Content inputs: service scope, pricing, customer details from Company Management and Sales.
- Customer response: accepted, rejected, or revision requested.

**Outputs**
- Proposal document stored in Google Drive (via Integrations).
- Proposal record in the system with status and linked Drive URL.
- Event: `proposal.accepted` — consumed by Sales and Contracts.
- Event: `proposal.rejected` — returned to Sales for follow-up.
- Notification to the team on status changes.

**Depends On**
- Identity & Access
- Company Management
- Sales
- Integrations

**Used By**
- Sales
- Contracts
- Notifications

---

## 6. Contracts

**Purpose**
Record and manage the binding agreements between Coder Link and its customers.

**Responsibilities**
- Create a Contract record when a Proposal is accepted.
- Store the signed contract document in Google Drive via the Integrations module.
- Track contract status: Draft, Signed, Active, Completed, Terminated.
- Define the agreed scope, payment terms, and duration.
- Trigger Project creation and Billing record creation upon contract activation.
- Maintain the audit trail of contract changes.

**Inputs**
- Proposal acceptance event from Proposal Management.
- Signed contract document uploaded by an authorised user.
- Status updates from an authorised user.

**Outputs**
- Contract record available to Project Management and Billing & Subscriptions.
- Signed contract stored in Google Drive (via Integrations).
- Event: `contract.signed` — triggers Project creation and first Invoice generation.
- Notification to the team when a contract is signed or terminated.

**Depends On**
- Identity & Access
- Company Management
- Proposal Management
- Integrations

**Used By**
- Project Management
- Billing & Subscriptions
- Notifications

---

## 7. Project Management

**Purpose**
Track the delivery of every sold project from start to customer acceptance.

**Responsibilities**
- Create a Project record when a Contract is signed.
- Track project status, milestones, and completion date.
- Assign tasks to Employees with due dates and priorities.
- Record all project activities and communications.
- Trigger the creation of the project folder in Google Drive via Integrations.
- Mark handover and trigger the Support or Maintenance flow upon completion.

**Inputs**
- Contract signed event from Contracts.
- Task updates, status changes, and notes from assigned Employees.
- Customer acceptance confirmation from an Account Manager.

**Outputs**
- Project records for visibility and reporting.
- Task records assigned to Employees.
- Event: `project.delivered` — triggers Support or Maintenance initiation.
- Event: `project.milestone.reached` — consumed by Notifications.
- Final Invoice trigger sent to Billing & Subscriptions.

**Depends On**
- Identity & Access
- Company Management
- Contracts
- Integrations

**Used By**
- Billing & Subscriptions
- Support & Maintenance
- Notifications
- Administration

---

## 8. Billing & Subscriptions

**Purpose**
Manage all invoices and recurring revenue records for every customer.

**Responsibilities**
- Generate Invoices triggered by contract signing, project milestones, and project delivery.
- Track Invoice status: Draft, Issued, Partially Paid, Paid, Overdue, Cancelled.
- Manage Maintenance Contracts and Hosting Subscriptions with renewal dates and pricing.
- Trigger renewal reminders at defined intervals before expiry via Notifications.
- Record payment confirmations and update invoice status.
- Surface upcoming renewals with upsell potential.

**Inputs**
- Invoice trigger from Contracts or Project Management.
- Subscription record creation when a recurring service is activated.
- Payment confirmation entered by an authorised user.
- Renewal date approaching — time-based trigger.

**Outputs**
- Invoice records with status and amounts.
- Subscription records with renewal dates.
- Event: `invoice.overdue` — consumed by Notifications.
- Event: `subscription.renewal.due` — consumed by Notifications and Sales.
- Renewal and overdue alerts to the Notifications module.

**Depends On**
- Identity & Access
- Company Management
- Contracts
- Project Management

**Used By**
- Support & Maintenance
- Notifications
- Sales (for upsell identification)
- Administration

---

## 9. Support & Maintenance

**Purpose**
Manage ongoing support obligations and maintenance contract delivery after a project is complete.

**Responsibilities**
- Create a support or maintenance record when a Project is delivered.
- Track support requests, resolution status, and SLA compliance.
- Manage scheduled maintenance tasks for active Maintenance Contracts.
- Record all support activities against the customer account.
- Flag contracts approaching expiry to the Billing & Subscriptions module.

**Inputs**
- Project delivered event from Project Management.
- Maintenance Contract record from Billing & Subscriptions.
- Support request submitted by an authorised user on behalf of the customer.
- Scheduled maintenance due date trigger.

**Outputs**
- Support request records with status and resolution notes.
- Maintenance activity records.
- Event: `support.request.overdue` — consumed by Notifications.
- Activity log entries on the Company record.

**Depends On**
- Identity & Access
- Company Management
- Project Management
- Billing & Subscriptions

**Used By**
- Notifications
- Administration

---

## 10. Notifications

**Purpose**
Deliver timely, relevant alerts and reminders to the right team members at the right time.

**Responsibilities**
- Receive events from all business modules and route them to the correct recipients.
- Send internal team notifications: overdue follow-ups, upcoming renewals, invoice alerts, project milestones.
- Send customer-facing notifications: proposal sent, follow-up messages.
- Support multiple delivery channels: Gmail, WhatsApp (via Integrations).
- Respect user notification preferences and role-based routing.

**Inputs**
- Events from all modules: lead overdue, proposal sent, contract signed, invoice overdue, subscription renewal due, project milestone, support request overdue.
- Notification configuration from Administration.

**Outputs**
- Delivered notifications via Gmail or WhatsApp (through Integrations).
- Notification delivery log.

**Depends On**
- Identity & Access
- Integrations

**Used By**
- No module depends on Notifications. It is a terminal consumer of events.

---

## 11. Integrations

**Purpose**
Connect Coder Link Business OS to external tools and services through managed, version-controlled integration points.

**Responsibilities**
- Manage the connection to Google Drive: create customer folders, upload documents, return file links.
- Manage the connection to Gmail: send outbound emails, receive inbound lead emails.
- Manage the connection to WhatsApp: send automated messages via the approved provider.
- Manage the connection to AI providers: pass prompts, receive generated content, return results to the AI Services module.
- Ensure no other module calls external services directly.
- All integration logic runs through n8n workflows, version-controlled in GitHub.

**Inputs**
- Requests from all modules to perform an external action (create folder, send email, send message, call AI).

**Outputs**
- Confirmation and result data returned to the requesting module.
- File links, message delivery confirmations, AI-generated content.
- Integration event logs.

**Depends On**
- Identity & Access

**Used By**
- Company Management
- Proposal Management
- Contracts
- Project Management
- Notifications
- AI Services

---

## 12. AI Services

**Purpose**
Augment team productivity by providing AI-generated assistance for defined, repeatable tasks.

**Responsibilities**
- Draft initial responses to inbound leads based on the service requested.
- Classify and score leads based on defined criteria.
- Generate first-draft proposal content from structured requirements input.
- Summarise activity history before a sales meeting.
- Generate WhatsApp follow-up message drafts at defined pipeline stages.
- Route all AI calls through the Integrations module; never call AI providers directly.
- Ensure all AI outputs are reviewed by a team member before being sent externally, unless a specific flow has been explicitly approved for full automation.

**Inputs**
- Trigger from CRM, Sales, or Proposal Management.
- Structured context data: lead details, service requested, company profile, activity history.

**Outputs**
- Draft text returned to the requesting module for review.
- Lead classification or score returned to CRM.
- AI generation event log.

**Depends On**
- Identity & Access
- CRM
- Sales
- Integrations

**Used By**
- CRM
- Sales
- Proposal Management
- Notifications

---

## 13. Administration

**Purpose**
Provide system-wide configuration and oversight for authorised administrators.

**Responsibilities**
- Manage user accounts and role assignments (through Identity & Access).
- Configure system-wide settings: notification rules, pipeline stage definitions, service catalog entries, subscription types.
- Monitor system health: error logs, failed automations, integration status.
- Provide audit log access for all significant system events.
- Manage n8n workflow activation and deactivation.

**Inputs**
- Configuration changes from an Administrator.
- System event and error data from all modules.

**Outputs**
- Updated system configuration applied across all modules.
- Audit log views for review.
- User account and role changes propagated to Identity & Access.

**Depends On**
- Identity & Access
- All other modules (for monitoring and configuration).

**Used By**
- Nothing depends on Administration. It is the top-level supervisory module.

---

## Module Dependency Diagram (text only)

Modules are listed from the lowest level (no dependencies) to the highest level (depends on most others). An arrow `→` means "is depended on by."

```
Level 0 — Foundation
─────────────────────
Identity & Access
  → All other modules

Level 1 — Core Data
─────────────────────
Company Management
  → CRM, Sales, Proposal Management, Contracts,
    Project Management, Billing & Subscriptions,
    Support & Maintenance, Notifications, Integrations

Level 2 — Pipeline Entry
─────────────────────────
CRM
  → Sales, Notifications, AI Services

Level 3 — Commercial Progression
──────────────────────────────────
Sales
  → Proposal Management, Contracts, Project Management,
    Notifications, AI Services, Administration

Integrations
  → Company Management, Proposal Management, Contracts,
    Project Management, Notifications, AI Services

Level 4 — Deal Closure
───────────────────────
Proposal Management
  → Sales, Contracts, Notifications

Level 5 — Commitment & Delivery
─────────────────────────────────
Contracts
  → Project Management, Billing & Subscriptions, Notifications

AI Services
  → CRM, Sales, Proposal Management, Notifications

Level 6 — Execution
─────────────────────
Project Management
  → Billing & Subscriptions, Support & Maintenance,
    Notifications, Administration

Level 7 — Recurring Revenue & Retention
─────────────────────────────────────────
Billing & Subscriptions
  → Support & Maintenance, Notifications,
    Sales (upsell), Administration

Level 8 — Post-Delivery
─────────────────────────
Support & Maintenance
  → Notifications, Administration

Level 9 — Communication (terminal)
────────────────────────────────────
Notifications
  → No module depends on Notifications

Level 10 — Oversight (top)
───────────────────────────
Administration
  → No module depends on Administration
```

**Reading the diagram:**
- Lower-level modules must be stable before higher-level modules are built.
- A module should never import logic from a module above it in this order.
- Changes to a lower-level module can affect all modules above it.
- The recommended build order for Phase 0 production follows this hierarchy from Level 0 upward.
