# Sales Pipeline

This document defines the complete sales pipeline for Coder Link — from the moment
a lead arrives to long-term customer retention and growth. It is a business-level
design only. No implementation, database, or code details are included.

**Status:** Draft

---

## Pipeline Overview

```
New Lead → Contact Attempt → Qualified → Discovery Meeting →
Requirements Gathering → Solution Design → Proposal Sent →
Negotiation → Won | Lost

Won → Project Started → Project Delivered → Support →
Maintenance → Renewal → Upsell
```

---

## Stage 1 — New Lead

**Purpose**
Register every inbound enquiry so no potential customer is lost.

**Entry Criteria**
A lead record exists with at least a name, phone or email, and the service enquired about.

**Exit Criteria**
A first contact attempt has been made or scheduled.

**Responsible Role**
Sales Representative

**Possible Next Stage**
Contact Attempt

---

## Stage 2 — Contact Attempt

**Purpose**
Reach the lead and establish a live conversation.

**Entry Criteria**
The lead has been assigned to a Sales Representative and contact has not yet been confirmed.

**Exit Criteria**
- Successful contact made → move to Qualified.
- No response after a defined number of attempts → mark as Lost or archive.

**Responsible Role**
Sales Representative

**Possible Next Stage**
Qualified | Lost

---

## Stage 3 — Qualified

**Purpose**
Confirm the lead has a real need, a budget, and the authority to decide.

**Entry Criteria**
A live conversation has taken place and initial details have been collected.

**Exit Criteria**
The lead meets qualification criteria: need confirmed, budget range understood, decision-maker identified.

**Responsible Role**
Sales Representative

**Possible Next Stage**
Discovery Meeting | Lost

---

## Stage 4 — Discovery Meeting

**Purpose**
Understand the customer's business, challenges, and goals in depth.

**Entry Criteria**
Lead is qualified and a meeting has been scheduled.

**Exit Criteria**
Meeting completed and notes recorded. Requirements gathering is ready to begin.

**Responsible Role**
Sales Representative / Account Manager

**Possible Next Stage**
Requirements Gathering | Lost

---

## Stage 5 — Requirements Gathering

**Purpose**
Document the full scope of what the customer needs before any solution is designed.

**Entry Criteria**
Discovery meeting is complete and the customer has agreed to proceed to scoping.

**Exit Criteria**
A requirements document is complete and confirmed with the customer.

**Responsible Role**
Account Manager / Technical Lead

**Possible Next Stage**
Solution Design | Lost

---

## Stage 6 — Solution Design

**Purpose**
Define the solution, scope of work, timeline, and pricing that will be presented to the customer.

**Entry Criteria**
Confirmed requirements document exists.

**Exit Criteria**
An internal solution design is approved and ready to be packaged into a Proposal.

**Responsible Role**
Technical Lead / Account Manager

**Possible Next Stage**
Proposal Sent

---

## Stage 7 — Proposal Sent

**Purpose**
Present the formal offer to the customer for review.

**Entry Criteria**
Solution design is complete and the Proposal document has been prepared and reviewed internally.

**Exit Criteria**
The customer has received the Proposal and provided a response.

**Responsible Role**
Account Manager / Sales Representative

**Possible Next Stage**
Negotiation | Won | Lost

---

## Stage 8 — Negotiation

**Purpose**
Reach final agreement on scope, price, and terms.

**Entry Criteria**
The customer has reviewed the Proposal and raised questions, objections, or counter-terms.

**Exit Criteria**
Both parties reach agreement → Won, or discussions break down → Lost.

**Responsible Role**
Account Manager / Sales Representative

**Possible Next Stage**
Won | Lost

---

## Stage 9 — Won

**Purpose**
Record a successfully closed deal and hand it over for delivery.

**Entry Criteria**
Customer has verbally or formally accepted the Proposal or agreed terms.

**Exit Criteria**
Contract is signed or payment confirmed. Project is ready to be started.

**Responsible Role**
Account Manager

**Possible Next Stage**
Project Started

---

## Stage 10 — Lost

**Purpose**
Record deals that did not close and preserve the reason for future learning.

**Entry Criteria**
The customer declined, went silent, chose a competitor, or the opportunity was disqualified.

**Exit Criteria**
Loss reason is recorded. The record is closed. A follow-up date may be set for future re-engagement.

**Responsible Role**
Sales Representative / Account Manager

**Possible Next Stage**
New Lead (future re-engagement)

---

## Stage 11 — Project Started

**Purpose**
Confirm delivery has begun and the customer is being actively served.

**Entry Criteria**
Contract is signed, first payment received or agreed, and the project has been assigned to the delivery team.

**Exit Criteria**
All deliverables are completed and the customer has accepted the output.

**Responsible Role**
Project Manager / Technical Lead

**Possible Next Stage**
Project Delivered

---

## Stage 12 — Project Delivered

**Purpose**
Confirm the project is complete and the customer has accepted the result.

**Entry Criteria**
All deliverables have been handed over and customer acceptance has been confirmed.

**Exit Criteria**
Final invoice is issued and the customer has been onboarded to any ongoing services.

**Responsible Role**
Project Manager / Account Manager

**Possible Next Stage**
Support | Maintenance | Hosting Subscription (Renewal)

---

## Stage 13 — Support

**Purpose**
Provide post-delivery technical assistance during any warranty or support period.

**Entry Criteria**
Project has been delivered and a support period is active (as defined in the Contract).

**Exit Criteria**
The support period expires or transitions into a formal Maintenance Contract.

**Responsible Role**
Technical Support / Account Manager

**Possible Next Stage**
Maintenance | Renewal

---

## Stage 14 — Maintenance

**Purpose**
Provide ongoing managed services under a recurring Maintenance Contract.

**Entry Criteria**
Customer has signed a Maintenance Contract.

**Exit Criteria**
The contract reaches its renewal date.

**Responsible Role**
Technical Support / Account Manager

**Possible Next Stage**
Renewal | Upsell | Lost (if not renewed)

---

## Stage 15 — Renewal

**Purpose**
Retain the customer by renewing an expiring Maintenance Contract or Hosting Subscription.

**Entry Criteria**
An active Maintenance Contract or Hosting Subscription is approaching its expiry date.

**Exit Criteria**
The customer renews → contract continues. The customer declines → record closed as churned.

**Responsible Role**
Account Manager / Sales Representative

**Possible Next Stage**
Maintenance | Upsell | Lost

---

## Stage 16 — Upsell

**Purpose**
Grow the customer relationship by offering additional or upgraded services.

**Entry Criteria**
The customer is active, satisfied, and has a potential need for a service not currently purchased.

**Exit Criteria**
Customer accepts the new offer → a new Opportunity is created and progressed through the pipeline. Customer declines → relationship continues at current level.

**Responsible Role**
Account Manager / Sales Representative

**Possible Next Stage**
New Lead (new Opportunity) | Maintenance | Renewal
