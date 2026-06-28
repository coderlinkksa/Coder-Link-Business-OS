# Laravel Module Structure

This document defines the folder organisation and architectural boundaries for the
Coder Link Business OS Laravel application. It is a design document only. No code,
classes, routes, or migrations are included.

**Status:** Draft

---

## 1. Module Boundaries

The Laravel application is organised into self-contained modules that map directly
to the business modules defined in `docs/SYSTEM_MODULES.md`. Each module is a
bounded context: it owns its domain logic, its data access, and its internal
interfaces. No module reaches into the internals of another.

The thirteen modules are:

| Module | Folder Name |
|--------|-------------|
| Identity & Access | `Identity` |
| Company Management | `Company` |
| CRM | `CRM` |
| Sales | `Sales` |
| Proposal Management | `Proposal` |
| Contracts | `Contract` |
| Project Management | `Project` |
| Billing & Subscriptions | `Billing` |
| Support & Maintenance | `Support` |
| Notifications | `Notification` |
| Integrations | `Integration` |
| AI Services | `AI` |
| Administration | `Admin` |

All modules live under a top-level `app/Modules/` directory. The Laravel default
`app/` structure is preserved for framework-level concerns only.

**Top-level structure:**

```
app/
├── Modules/
│   ├── Identity/
│   ├── Company/
│   ├── CRM/
│   ├── Sales/
│   ├── Proposal/
│   ├── Contract/
│   ├── Project/
│   ├── Billing/
│   ├── Support/
│   ├── Notification/
│   ├── Integration/
│   ├── AI/
│   └── Admin/
├── Shared/
└── Providers/
```

---

## 2. Shared Kernel

The Shared Kernel contains code that is legitimately used by all modules without
creating a coupling between them. It does not contain business logic.

```
app/Shared/
├── Contracts/       Interfaces that modules implement or depend on
├── Events/          Base event classes and shared event contracts
├── Exceptions/      Base exception classes
├── Models/          Abstract base model with shared behaviour (soft delete, audit stamps)
├── Traits/          Reusable traits: HasAuditLog, SoftDeletable, HasOwnerReference
├── ValueObjects/    Shared value objects: Money, PhoneNumber, EmailAddress
└── Support/         General-purpose helpers used across modules
```

**Rules for the Shared Kernel:**
- No Shared Kernel file may import from any module.
- Modules may import from Shared Kernel freely.
- Business logic never lives in the Shared Kernel.
- The Shared Kernel grows slowly and deliberately; every addition must be justified.

---

## 3. Application Layer

The Application Layer within each module contains the use cases — the actions the
system can perform. It orchestrates domain objects and infrastructure without
containing business rules itself.

Each module's Application Layer:

```
app/Modules/{Module}/Application/
├── Actions/         Single-purpose action classes: one class per use case
├── DTOs/            Data transfer objects for passing structured data between layers
├── Queries/         Read-only query handlers for retrieving data
└── Services/        Orchestration services when a use case spans multiple domain objects
```

**Rules for the Application Layer:**
- Actions and services coordinate; they do not contain business rules.
- Business rules belong in the Domain Layer.
- The Application Layer calls the Domain Layer and the Infrastructure Layer.
- The Application Layer does not call another module's Application Layer directly.
  Cross-module communication happens through events or published contracts.

---

## 4. Domain Layer

The Domain Layer is the heart of each module. It contains the business rules,
entity definitions, and domain logic that represent how the business works.

Each module's Domain Layer:

```
app/Modules/{Module}/Domain/
├── Models/          Eloquent models representing domain entities
├── Enums/           Status enumerations: LeadStatus, OpportunityStage, InvoiceStatus
├── Events/          Domain events raised when something significant happens
├── Exceptions/      Domain-specific exceptions: LeadAlreadyConverted, ProposalExpired
├── Policies/        Authorisation rules: who can do what with which records
└── Rules/           Business validation rules used in domain operations
```

**Rules for the Domain Layer:**
- Domain models contain business behaviour, not just data.
- Status transitions are enforced here, not in controllers.
- Domain events are raised here and consumed by listeners in the Infrastructure Layer or other modules.
- The Domain Layer has no knowledge of HTTP, queues, or external services.
- Domain models do not call repositories from other modules.

---

## 5. Infrastructure Layer

The Infrastructure Layer handles communication with external systems and framework
concerns: the database, file storage, caches, queues, and third-party services.

Each module's Infrastructure Layer:

```
app/Modules/{Module}/Infrastructure/
├── Repositories/    Data access implementations
├── Listeners/       Event listeners that react to domain events
├── Jobs/            Queued background jobs triggered by the module
├── Observers/       Eloquent model observers for cross-cutting persistence concerns
└── Persistence/     Query scopes, casts, and data mapping concerns
```

**Rules for the Infrastructure Layer:**
- Repositories implement interfaces defined in Shared/Contracts.
- Listeners in one module may listen to events raised by another module.
  This is the primary mechanism for cross-module side effects.
- Jobs handle work that must not block the request lifecycle.
- No Infrastructure Layer file contains business logic.

---

## 6. API Layer

The API Layer exposes the module's functionality to the outside world. In the first
version, this means the internal web interface and the endpoints consumed by n8n.
There is no public API in scope.

Each module's API Layer:

```
app/Modules/{Module}/API/
├── Controllers/     HTTP controllers: one per resource or action group
├── Requests/        Form request validation: one per controller action
├── Resources/       API resource transformers for JSON responses
└── Routes/          Route definitions for this module only
```

**Rules for the API Layer:**
- Controllers contain no business logic. They validate input, call an Action, and return a response.
- All input validation is handled by Request classes, not in controllers.
- All output is formatted by Resource classes, not assembled in controllers.
- Each module's routes are registered in its own routes file and loaded by a service provider.
- Routes for the internal web interface and the n8n integration are kept separate.

---

## 7. Console Layer

The Console Layer contains scheduled commands and manual administrative commands.

Each module's Console Layer:

```
app/Modules/{Module}/Console/
└── Commands/        Artisan commands for scheduled jobs and administrative operations
```

**Rules for the Console Layer:**
- Commands contain no business logic. They call Actions or Services from the Application Layer.
- Scheduled commands are registered in a central schedule definition, not scattered across modules.
- Commands intended for automation are clearly separated from commands intended for manual use.

---

## 8. Event System

The event system is the primary mechanism for communication between modules.
When a significant business action occurs in one module, it raises a domain event.
Other modules that need to react to that event register listeners.

**Event naming convention:**
Events are named in past tense to indicate something that has already occurred.

Format: `{Module}\Domain\Events\{EntityName}{PastTenseVerb}`

Examples:
- `CRM\Domain\Events\LeadQualified`
- `Sales\Domain\Events\OpportunityWon`
- `Proposal\Domain\Events\ProposalAccepted`
- `Contract\Domain\Events\ContractSigned`
- `Project\Domain\Events\ProjectDelivered`
- `Billing\Domain\Events\InvoiceOverdue`
- `Billing\Domain\Events\SubscriptionRenewalDue`

**Event rules:**
- Events are raised by Domain Models or Application Layer Actions.
- Events carry only the data needed to identify what happened: entity type, identifier, and relevant context.
- Events do not carry entire model objects; listeners load what they need.
- Listeners are registered in each module's service provider.
- Cross-module listeners live in the Infrastructure Layer of the listening module, not the raising module.
- Events may be queued for asynchronous processing when the listener performs slow work.

---

## 9. Notification Layer

Notifications are managed through Laravel's notification system. The Notification
module owns the delivery configuration; individual modules raise the events that
trigger notifications.

```
app/Modules/Notification/
├── Application/
│   └── Actions/     SendNotification, ScheduleNotification
├── Domain/
│   ├── Models/      NotificationLog, NotificationPreference
│   └── Enums/       NotificationChannel, NotificationStatus
└── Infrastructure/
    ├── Listeners/   Listen to events from all modules and dispatch notifications
    └── Jobs/        Queue notification delivery
```

**Notification rules:**
- Business modules do not send notifications directly. They raise events.
- The Notification module's listeners receive those events and determine how and when to notify.
- Notification delivery channels (Gmail, WhatsApp) are handled through the Integration module.
- All notification attempts and outcomes are logged in the Notification module's data area.
- Notification preferences per user or role are configurable through the Admin module.

---

## 10. File Storage Layer

File management is handled through the Integration module, which communicates with
Google Drive as decided in ADR-005. The file storage layer within the application
manages file reference records and coordinates upload requests.

```
app/Modules/Integration/
└── Application/
    └── Actions/
        ├── CreateDriveFolder
        ├── UploadFileToDrive
        └── StoreFileReference
```

**File storage rules:**
- No module stores file content in the database.
- Every module that produces a file (proposals, contracts, deliverables) requests
  an upload through the Integration module's Actions.
- The file reference record (name, Drive identifier, shareable link, status) is
  stored in the requesting module's data area after the upload completes.
- File reference records are subject to soft delete and audit logging.
- Drive folder creation is triggered by the Company module when a new company is created.

---

## 11. Integration Layer

The Integration module acts as the single gateway between the Laravel application
and all external services. No other module calls an external API directly.

```
app/Modules/Integration/
├── Application/
│   ├── Actions/     One Action per external operation: CreateDriveFolder, SendGmailNotification, etc.
│   └── DTOs/        Structured payloads for each integration type
├── Domain/
│   ├── Models/      IntegrationEventLog
│   └── Enums/       IntegrationService, IntegrationStatus
└── Infrastructure/
    ├── Listeners/   Listen to integration-triggering events from other modules
    └── Jobs/        Queue integration calls for asynchronous execution
```

**Integration rules:**
- All external API calls are made by the Integration module. No exception.
- Integration Actions log every call: service, action, payload summary, outcome, timestamp.
- Failed integrations are logged and may trigger a retry job or a notification to the Admin module.
- n8n is the runtime for integration execution. Laravel raises the event; n8n receives it via a webhook and performs the external action; n8n calls a Laravel API endpoint to record the result.
- Credentials for external services are never stored in the database or in code. They are injected via environment configuration.

---

## 12. Testing Strategy

Each module is independently testable. The testing structure mirrors the module structure.

```
tests/
├── Unit/
│   └── Modules/
│       └── {Module}/
│           ├── Domain/        Unit tests for domain models, enums, rules, policies
│           └── Application/   Unit tests for actions and services with mocked dependencies
├── Feature/
│   └── Modules/
│       └── {Module}/          Feature tests covering full request-response cycles
├── Integration/               Tests covering cross-module event flows
└── Shared/                    Tests for Shared Kernel components
```

**Testing rules:**
- Domain Layer tests are pure unit tests: no database, no HTTP, no external services.
- Application Layer tests mock infrastructure dependencies.
- Feature tests use a real database (test database, not production) and test the full
  stack from HTTP request to database state.
- Integration tests verify that events raised by one module trigger the correct
  behaviour in listening modules.
- No test connects to external services. Google Drive, Gmail, WhatsApp, and AI
  providers are mocked at the Integration module boundary.
- Every status transition defined in the domain must have a corresponding test.

---

## 13. Naming Conventions

Consistent naming across the application makes the system legible to any team member.

**Modules:**
Named after the business domain in singular form: `CRM`, `Sales`, `Proposal`, `Contract`, `Project`, `Billing`, `Support`, `Notification`, `Integration`, `AI`, `Admin`, `Identity`, `Company`.

**Actions:**
Named as `{Verb}{Noun}` in present tense, describing the operation precisely.
Examples: `CreateLead`, `QualifyLead`, `ConvertLeadToOpportunity`, `SendProposal`, `MarkInvoicePaid`.

**Events:**
Named as `{Noun}{PastTenseVerb}`, always in the module's `Domain/Events/` folder.
Examples: `LeadCreated`, `LeadQualified`, `OpportunityWon`, `ProposalSent`, `InvoicePaid`.

**DTOs:**
Named as `{Noun}Data` or `{Action}Data`.
Examples: `CreateLeadData`, `ProposalData`, `InvoiceLineItemData`.

**Enums:**
Named as `{Noun}Status` or `{Noun}Stage` or `{Noun}Type`.
Examples: `LeadStatus`, `OpportunityStage`, `InvoiceStatus`, `NotificationChannel`.

**Requests:**
Named as `{Action}{Noun}Request`.
Examples: `CreateLeadRequest`, `UpdateOpportunityRequest`, `SendProposalRequest`.

**Resources:**
Named as `{Noun}Resource` or `{Noun}Collection`.
Examples: `LeadResource`, `OpportunityResource`, `CompanyCollection`.

**Commands:**
Named as `{Module}:{action}-{noun}` in kebab-case.
Examples: `crm:send-overdue-followup-alerts`, `billing:process-renewal-reminders`.

**Listeners:**
Named as `{Verb}{WhatHappened}` describing the reaction.
Examples: `CreateCompanyDriveFolder`, `NotifyTeamOfNewLead`, `GenerateRenewalInvoice`.

---

## Module Dependency Rules

These rules define which modules may depend on others and which dependencies are
explicitly forbidden. A dependency means one module's code imports or directly
calls another module's code.

**Permitted dependencies (by direction):**

```
Identity        ← no module imports Identity directly; all modules use it via middleware
Shared Kernel   ← all modules may import from Shared Kernel
Company         ← CRM, Sales, Proposal, Contract, Project, Billing, Support, Notification, Integration, Admin
CRM             ← Sales, AI, Notification
Sales           ← Proposal, Contract, Project, Notification, AI, Admin
Proposal        ← Contract, Sales, Notification, Integration
Contract        ← Project, Billing, Notification, Integration
Project         ← Billing, Support, Notification, Integration, Admin
Billing         ← Support, Notification, Sales (read-only, for upsell surface)
Support         ← Notification, Admin
Integration     ← Notification, AI (for delivering AI results via external channels)
Notification    ← Integration (for channel delivery)
AI              ← Integration (for calling AI providers)
Admin           ← all modules (read-only, for monitoring and configuration)
```

**Forbidden dependencies:**

| Forbidden | Reason |
|-----------|--------|
| Any module importing from a higher-level module's internals | Violates the dependency hierarchy defined in SYSTEM_MODULES.md |
| Identity imported directly by business module code | Identity enforcement is handled by middleware, not inline module code |
| Integration calling another module's Application Layer directly | Integration is a downstream actor; it records results through events or API callbacks |
| Two peer-level modules importing each other (circular dependency) | Circular dependencies must be broken by extracting to a shared contract or using events |
| Any module accessing another module's Domain Models directly | Cross-module data access happens through Actions, APIs, or events — never direct model queries |
| Business logic placed in the Shared Kernel | The Shared Kernel contains structural code only |
| Database queries placed in Controllers or Actions | Queries belong in Repositories within the Infrastructure Layer |

**The golden rule:**
A module may observe events raised by any other module. A module may call the
published Actions of a module it explicitly depends on. A module may never reach
into the internal Domain Models, Repositories, or private services of another module.
