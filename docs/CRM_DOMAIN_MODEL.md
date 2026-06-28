# CRM Domain Model

This document defines the business entities that form the CRM domain for Coder Link.
It is a business-level design only. No implementation, database, or code details are included.

**Status:** Draft

---

## 1. Lead

**Purpose**
Represent a potential customer who has expressed interest but has not yet been qualified.

**Description**
A Lead is the entry point into the CRM pipeline. It captures the initial contact information and the service the potential customer enquired about. Every inbound enquiry — from a website form, referral, or any other source — becomes a Lead.

**Relationships**
- Converts into a Company and/or Contact Person upon qualification.
- Converts into an Opportunity when the lead is deemed worth pursuing.
- May have Activities and Follow-ups attached to it.
- Assigned to an Employee responsible for qualification.

**Business Lifecycle**
New → Contacted → Qualified → Converted (to Opportunity) | Disqualified

---

## 2. Company

**Purpose**
Represent a business organisation that is, or may become, a customer of Coder Link.

**Description**
A Company is the primary account record. It holds all information about the business entity: name, industry, location, size, and account status. Multiple Contact Persons may belong to a single Company.

**Relationships**
- Has one or more Contact Persons.
- Has one or more Opportunities.
- Has Proposals, Contracts, Projects, Invoices, Maintenance Contracts, and Hosting Subscriptions linked to it.
- Originated from a qualified Lead.
- Managed by an assigned Employee.

**Business Lifecycle**
Prospect → Active Customer → Inactive | Churned

---

## 3. Contact Person

**Purpose**
Represent an individual who acts as a point of contact within a Company.

**Description**
A Contact Person is a human within a Company who Coder Link communicates with. A Company may have multiple Contact Persons (e.g. the decision-maker and the technical contact). Every communication is directed at a specific Contact Person.

**Relationships**
- Belongs to one Company.
- May be the primary contact for one or more Opportunities.
- Has Activities and Follow-ups associated with them.
- Originated from a qualified Lead.

**Business Lifecycle**
Active → Inactive (if they leave the company or the relationship ends)

---

## 4. Opportunity

**Purpose**
Represent a specific, qualified sales chance with a defined service scope and expected value.

**Description**
An Opportunity is created when a Lead or an existing customer has a concrete need that Coder Link can address. It tracks the probability of winning, the expected value, and the stage of the negotiation. It is the central record of the sales process.

**Relationships**
- Belongs to a Company and linked to one or more Contact Persons.
- Originated from a Lead.
- Leads to one or more Proposals.
- When won, results in a Contract or Project.
- Has Activities, Follow-ups, and Tasks attached.
- Assigned to an Employee.

**Business Lifecycle**
Qualification → Needs Analysis → Proposal Sent → Negotiation → Won | Lost

---

## 5. Proposal

**Purpose**
Represent a formal offer sent to a Company describing the solution and its price.

**Description**
A Proposal documents the scope of work, deliverables, timeline, and pricing that Coder Link presents to a prospect or customer. It may go through multiple revisions before being accepted or declined.

**Relationships**
- Linked to one Opportunity.
- Linked to one Company and one or more Contact Persons.
- When accepted, leads to a Contract.
- Prepared and owned by an Employee.

**Business Lifecycle**
Draft → Sent → Under Review → Accepted | Rejected | Expired

---

## 6. Contract

**Purpose**
Represent a legally agreed commitment between Coder Link and a customer.

**Description**
A Contract is created when a Proposal is accepted. It defines the agreed scope, price, payment terms, and duration. It is the binding record that authorises work to begin.

**Relationships**
- Linked to one Proposal and one Company.
- Triggers the creation of a Project and/or a Maintenance Contract or Hosting Subscription.
- Generates one or more Invoices.
- Managed by an Employee.

**Business Lifecycle**
Draft → Signed → Active → Completed | Terminated

---

## 7. Project

**Purpose**
Represent a unit of delivery work carried out for a customer under a Contract.

**Description**
A Project is the execution record for a defined piece of work — a website build, software development engagement, or infrastructure setup. It tracks progress, milestones, and completion.

**Relationships**
- Linked to one Contract and one Company.
- Has Tasks assigned to Employees.
- Has Activities and Follow-ups attached.
- Completion may trigger Invoice generation.
- May lead to a Maintenance Contract or Hosting Subscription upon delivery.

**Business Lifecycle**
Not Started → In Progress → Under Review → Delivered → Closed

---

## 8. Invoice

**Purpose**
Represent a payment request issued to a customer.

**Description**
An Invoice records the amount owed, the services it covers, the due date, and the payment status. Invoices are generated from Contracts or recurring subscriptions.

**Relationships**
- Linked to one Company.
- Linked to a Contract, Project, Maintenance Contract, or Hosting Subscription.
- Tracked by an Employee.

**Business Lifecycle**
Draft → Issued → Partially Paid | Paid | Overdue | Cancelled

---

## 9. Maintenance Contract

**Purpose**
Represent an ongoing support agreement that generates recurring revenue.

**Description**
A Maintenance Contract is a renewable agreement under which Coder Link provides technical support, updates, or managed services for a fixed periodic fee. It is the primary vehicle for annual recurring revenue from IT and software services.

**Relationships**
- Linked to one Company.
- Originated from a completed Project or a signed Contract.
- Generates recurring Invoices.
- Has Tasks and Activities for scheduled maintenance visits or support tickets.
- Assigned to an Employee.

**Business Lifecycle**
Active → Up for Renewal → Renewed | Expired | Cancelled

---

## 10. Hosting Subscription

**Purpose**
Represent a recurring hosting, domain, or business email service purchased by a customer.

**Description**
A Hosting Subscription tracks the hosting plan, renewal date, and status for each customer. Renewals typically begin from the second year and are a predictable source of recurring revenue.

**Relationships**
- Linked to one Company.
- Generates recurring Invoices on the renewal date.
- Assigned to an Employee responsible for renewal follow-up.

**Business Lifecycle**
Active → Up for Renewal → Renewed | Expired | Cancelled

---

## 11. Task

**Purpose**
Represent a discrete action item that an Employee must complete.

**Description**
A Task is a specific, actionable item with a due date and an owner. Tasks keep work organised and ensure nothing falls through the cracks across Leads, Opportunities, Projects, and Maintenance Contracts.

**Relationships**
- May be linked to a Lead, Opportunity, Project, Maintenance Contract, or Company.
- Assigned to one Employee.
- May result in an Activity record when completed.

**Business Lifecycle**
Pending → In Progress → Completed | Cancelled

---

## 12. Activity

**Purpose**
Record every meaningful interaction or event that has occurred in relation to a customer or deal.

**Description**
An Activity is an immutable log entry that captures what happened: a call made, a meeting held, an email sent, a proposal delivered, a payment received. Activities form the historical record of the relationship.

**Relationships**
- Linked to a Lead, Company, Contact Person, Opportunity, Project, or Contract.
- Created by an Employee.
- May be generated automatically by a completed Task or a workflow event.

**Business Lifecycle**
Logged (Activities are permanent records; they are not updated or deleted.)

---

## 13. Follow-up

**Purpose**
Represent a scheduled reminder to re-engage with a Lead, Contact Person, or Opportunity at a future date.

**Description**
A Follow-up ensures that no lead or customer is forgotten. It is a time-bound commitment to take a specific action — call, message, or visit — by a defined date. Follow-ups are distinct from Tasks in that they are always customer-facing re-engagement actions.

**Relationships**
- Linked to a Lead, Contact Person, or Opportunity.
- Assigned to an Employee.
- Completing a Follow-up creates an Activity record.

**Business Lifecycle**
Scheduled → Completed | Rescheduled | Cancelled

---

## 14. Employee

**Purpose**
Represent a member of the Coder Link team who owns or is responsible for CRM records and actions.

**Description**
An Employee is an internal user of the CRM. Employees are assigned to Leads, Opportunities, Projects, Tasks, and Follow-ups. They are accountable for the outcomes of the records assigned to them.

**Relationships**
- Assigned to Leads, Opportunities, Projects, Tasks, Follow-ups, Maintenance Contracts, and Hosting Subscriptions.
- Creates Activities.
- Belongs to a role that determines what they can see and do.

**Business Lifecycle**
Active → Inactive (when they leave the company or change roles)
