# RBAC Specification

This document defines the complete Role-Based Access Control model for Coder Link
Business OS. It is a functional specification only. No code, migrations, or
implementation details are included.

**Status:** Draft

---

## 1. Objectives

The RBAC system must achieve the following business objectives:

- Ensure every user can see and do only what their role requires.
- Prevent any user from accessing, modifying, or deleting records they do not own or are not assigned to.
- Allow the Owner and Admins to configure who has access to what without writing code.
- Produce an auditable record of every access control decision that results in an action.
- Support the growth of the team from a single operator to multiple specialised roles.
- Be simple enough that a non-engineer can understand which role does what.
- Protect customer data and commercial information from unauthorised access.

---

## 2. Authentication vs Authorisation

These are two distinct concerns and must not be conflated.

**Authentication** answers: *Who is this person?*
It verifies the identity of the user attempting to access the system.
Authentication produces a confirmed identity: a verified user session.

**Authorisation** answers: *What is this person allowed to do?*
It determines whether the authenticated user has permission to perform a
specific action on a specific record.
Authorisation uses the identity produced by authentication plus the user's
assigned role and record ownership to grant or deny access.

**Rules:**
- No authorisation check is performed before authentication is confirmed.
- Every route and every action in the system is protected by an authorisation check.
- Authentication failure returns the user to the login screen.
- Authorisation failure returns a denied response with no sensitive detail exposed.
- Authorisation decisions are enforced server-side. The UI may hide options, but
  hiding is not a security control — the server always makes the final decision.

---

## 3. System Roles

The following roles are defined for the Coder Link Business OS internal system.
These are the roles that apply to Coder Link's own team members.

### Owner

The highest level of access. Reserved for the business owner.

- Full access to all modules, all records, and all configuration.
- Can create, modify, and delete any record in the system.
- Can assign any role to any user.
- Can access all financial and commercial data.
- Can view and export the audit log.
- Cannot be removed from the system by any other user.
- There is exactly one Owner account.

---

### Admin

Full operational access. Reserved for senior team members trusted to manage the system.

- Full access to all modules and all records.
- Can create and deactivate user accounts.
- Can assign roles up to but not including Owner.
- Can configure system settings, notification rules, and pipeline stage definitions.
- Can view the audit log.
- Cannot delete another Admin account; only the Owner can do this.

---

### Sales Representative

Access to the sales pipeline and lead management.

- Can create, view, and update Leads assigned to them.
- Can view all Leads (read-only) to support handoff.
- Can create and update Opportunities assigned to them.
- Can create Proposals linked to their Opportunities.
- Can record Activities and Follow-ups on their assigned records.
- Can create and complete Tasks on their assigned records.
- Cannot view or modify financial records (Invoices, Subscriptions).
- Cannot view or modify Contracts.
- Cannot delete any record.

---

### Account Manager

Access to the full customer relationship after a deal is won.

- All Sales Representative capabilities.
- Can view and update Company and Contact Person records.
- Can manage Proposals through all stages including acceptance.
- Can view Contracts linked to their accounts.
- Can view Invoice status and Subscription renewal dates for their accounts.
- Can manage Project records linked to their accounts.
- Can create and update Support records for their accounts.
- Cannot create or modify financial records (Invoices, Subscriptions).
- Cannot delete any record.

---

### Technical Support

Access to project delivery and post-delivery support.

- Can view Company and Contact Person records (read-only).
- Can view Projects assigned to them.
- Can update Task status on their assigned projects.
- Can create and update Support requests assigned to them.
- Can record Activities on support and maintenance records.
- Cannot view pipeline, commercial, or financial records.
- Cannot delete any record.

---

### Viewer

Read-only access for reporting or oversight purposes.

- Can view all records across all modules.
- Cannot create, update, or delete any record.
- Cannot access system configuration or audit log.
- Cannot be assigned to any record as an owner.

---

## 4. Company Roles

In the context of Coder Link Business OS v1, there is one company operating the system:
Coder Link. The concept of company roles is defined here to prepare for future
multi-tenancy where multiple companies (tenants) will use the system.

In the single-tenant version, all roles above apply to the single operating company.
The company context is recorded on every user account for forward compatibility.

When multi-tenancy is introduced, the following distinctions will apply:

**Platform-level roles** (set by the SaaS platform operator):
- Platform Owner — equivalent to the current Owner role, scoped to the entire platform.
- Platform Admin — manages tenants, billing, and platform configuration.

**Tenant-level roles** (set within each tenant company):
- Company Admin — manages users, configuration, and all records within their company.
- All other roles as defined in Section 3, scoped strictly to that tenant's records.

No tenant-level user may access records belonging to another tenant under any
circumstance. See Section 9.

---

## 5. Default Permissions

New user accounts are created without any role assigned. A role must be explicitly
granted by an Admin or Owner before the user can access any part of the system.

A user with no role assigned:
- Can authenticate (log in).
- Sees a dashboard informing them that their account is pending role assignment.
- Cannot access any module, record, or action.

When a role is assigned, the user inherits all permissions defined for that role.
Permissions are not additive by default — a user has exactly one role and inherits
that role's permissions only.

Exception: in future versions, an Admin may assign supplementary permission grants
to individual users within their role boundary. This is defined in Section 13.

---

## 6. Permission Categories

Permissions are organised into the following categories, aligned to the modules
defined in SYSTEM_MODULES.md.

| Category | Scope |
|----------|-------|
| `identity` | User accounts, role assignments, authentication events |
| `company` | Company records, contact person records |
| `crm` | Leads, activities, follow-ups, tasks (pipeline entry) |
| `sales` | Opportunities, stage transitions, deal outcomes |
| `proposal` | Proposal creation, revision, status, acceptance |
| `contract` | Contract records, status, terms |
| `project` | Project records, milestones, delivery tasks |
| `billing` | Invoices, subscriptions, maintenance contracts, renewals |
| `support` | Support requests, maintenance schedules |
| `notification` | Notification preferences, delivery logs |
| `integration` | Integration event logs, external service configuration |
| `ai` | AI generation logs, AI action triggers |
| `admin` | System configuration, audit log, user management |

---

## 7. Permission Naming Convention

Each permission is named using the following format:

```
{category}.{action}
```

Standard actions:

| Action | Meaning |
|--------|---------|
| `view` | Read any record in this category |
| `view-own` | Read only records assigned to the user or created by the user |
| `create` | Create new records in this category |
| `update` | Modify existing records in this category |
| `update-own` | Modify only records assigned to or owned by the user |
| `delete` | Soft-delete records in this category |
| `restore` | Restore soft-deleted records |
| `export` | Export data from this category |
| `configure` | Modify configuration settings for this category |

**Examples:**

```
crm.view-own
crm.create
crm.update-own
sales.view
sales.update-own
proposal.create
proposal.update-own
billing.view
billing.configure
admin.view
admin.configure
identity.configure
```

**Rules:**
- Permission names are lowercase, hyphenated, and use the dot separator.
- No permission is created without a defined role that uses it.
- New permissions are added when a new module or capability is formally specified.
- Permissions are not invented speculatively.

---

## 8. Ownership Rules

Ownership determines which records a user can act upon when their permission is
scoped to `view-own` or `update-own`.

**A user owns a record when:**
- They created the record, OR
- The record has been explicitly assigned to them by an Admin or Owner.

**Ownership rules:**
- Ownership is recorded at the point of creation and can be reassigned by an Admin or Owner.
- Reassigning ownership transfers all `own`-scoped permissions to the new owner.
- A record can have only one primary assigned owner at a time.
- Historical ownership is preserved in the audit log; changing the assigned user does not erase prior ownership history.
- The Owner and Admin roles bypass ownership restrictions and can act on all records.

**Shared visibility:**
- Sales Representatives may view (read-only) all Leads and Opportunities to support handoff and visibility.
- Account Managers may view all Company and Contact records for coordination.
- These shared view permissions do not grant edit access to records they do not own.

---

## 9. Cross-Tenant Restrictions

In the current single-tenant version, this section defines the principles that will
be enforced when multi-tenancy is introduced. They are stated now to prevent
architecture decisions that would violate them later.

**Absolute rules:**
- A user belonging to one tenant may never view, create, update, or delete records
  belonging to another tenant.
- Tenant isolation is enforced at the application layer, not solely by UI filtering.
  Every data query is scoped to the authenticated user's tenant.
- No API endpoint may return records from a tenant other than the requesting user's tenant.
- Integration credentials (Google Drive, Gmail, WhatsApp) are scoped per tenant.
  One tenant's credentials may never be used to perform actions for another tenant.
- The audit log is tenant-scoped. A tenant admin may only view audit records belonging to their tenant.
- Platform-level Admins and the Platform Owner may access tenant data only for
  support and compliance purposes, and every such access is logged.

---

## 10. Super Admin Capabilities

The Owner account has the following exclusive capabilities not available to any other role.

- Deactivate or delete any user account including Admin accounts.
- Assign or revoke the Admin role.
- View and export the complete audit log with no filtering restrictions.
- Access all financial data across all accounts and time periods.
- Configure system-wide integration credentials.
- Activate or deactivate system modules.
- Manage the service catalog and pipeline stage configuration.
- In a future multi-tenant version: manage tenant accounts, platform billing, and
  cross-tenant compliance data.

These capabilities are not delegatable. Only the Owner account holds them.

---

## 11. Company Admin Capabilities

In the current single-tenant version, the Admin role holds the company administration
responsibilities. In a future multi-tenant version, each tenant will have a designated
Company Admin role with the following capabilities scoped to their tenant only.

Company Admin capabilities:
- Create and deactivate user accounts within their company.
- Assign roles up to but not including Company Admin to users within their company.
- View all records within their company across all modules.
- Configure company-specific settings: pipeline stages, notification preferences,
  service catalog entries relevant to their company.
- View the audit log for their company's records.
- Cannot access platform-level configuration or other tenants' data.

---

## 12. Audit Requirements

Every authorisation decision that results in a significant action must be logged.

**What is audited:**

| Event | Detail Recorded |
|-------|-----------------|
| Login | User, timestamp, IP address, outcome (success or failure) |
| Logout | User, timestamp |
| Failed login attempt | User (if identified), timestamp, IP address |
| Record created | User, module, record identifier, timestamp |
| Record updated | User, module, record identifier, fields changed, previous and new values, timestamp |
| Record soft-deleted | User, module, record identifier, timestamp |
| Record restored | User, module, record identifier, timestamp |
| Role assigned | Performing user, target user, role assigned, timestamp |
| Role revoked | Performing user, target user, role revoked, timestamp |
| Permission denied | User, action attempted, resource, timestamp |
| System configuration changed | User, setting changed, previous and new value, timestamp |
| Audit log accessed | User, timestamp, filters applied |
| Data exported | User, module, record count, timestamp |

**Audit log rules:**
- Audit records are append-only. They are never modified or deleted.
- The audit log is retained indefinitely unless a formal data retention policy requires otherwise.
- Audit log access is restricted to Owner and Admin roles.
- Every audit record includes the user identifier, the action, the affected record,
  and the timestamp. No audit record is created without all four.
- Audit records are created synchronously before the action is confirmed as complete.
  A failed audit write must prevent the action from completing.

---

## 13. Future Custom Roles Strategy

In the first version, roles are fixed as defined in Section 3. Custom roles are not
supported. This is a deliberate constraint to keep the system simple while the
team is small.

The following strategy defines how custom roles will be introduced in a future version
when the team size or business complexity justifies it.

**Planned approach:**
- Roles will be composable from the permission set defined in Section 6 and 7.
- An Admin will be able to create a named custom role and assign individual permissions to it.
- Custom roles cannot exceed the permissions held by the Admin creating them
  (no privilege escalation through custom role creation).
- Custom roles are scoped to the tenant in which they are created.
- Platform-level roles (Owner, Platform Admin) remain fixed and non-customisable.
- The permission naming convention defined in Section 7 must be followed for any
  new permissions added to support custom roles.
- All custom role creation, modification, and deletion events are recorded in the audit log.

**What will not change:**
- The Owner role remains fixed, singular, and non-delegatable.
- Authorisation remains server-side enforced regardless of role type.
- The audit requirements in Section 12 apply equally to users with custom roles.
- Cross-tenant restrictions in Section 9 apply regardless of role type.
