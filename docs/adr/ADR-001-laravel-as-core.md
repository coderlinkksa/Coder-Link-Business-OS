# ADR-001 — Laravel as Core Business Application

**Status:** Accepted
**Date:** 2026-06-28

---

## Context

Coder Link Business OS requires a central application to own all business data,
enforce business rules, manage user authentication, and provide the internal team
interface. The system must be maintainable by a small team, operable without deep
engineering knowledge on a day-to-day basis, and capable of evolving as the business grows.

The first production priority is the Sales Engine and CRM. The system of record must
be a reliable, well-understood application — not a collection of scripts or automation
workflows.

---

## Decision

Laravel (PHP) is the core business application and the system of record for all
business data.

Laravel is responsible for all business entities, business rules, status transitions,
access control, the internal web interface, and the internal APIs consumed by the
automation layer.

---

## Consequences

**Positive**
- Mature, well-documented framework with a large ecosystem.
- Clear separation between business logic (Laravel) and automation (n8n).
- Standard patterns for authentication, authorisation, queues, and migrations.
- Suitable for the technical skill set available to the team.
- Well-suited to future SaaS expansion with multi-tenancy patterns.

**Negative**
- Requires PHP knowledge for any backend changes.
- A new hire unfamiliar with Laravel must learn the framework before contributing.

---

## Alternatives Considered

| Alternative | Reason Not Chosen |
|-------------|-------------------|
| Node.js / Express | Less opinionated; would require more custom structure decisions |
| Django (Python) | Different skill set; no existing team familiarity |
| No-code backend (e.g. Supabase) | Insufficient control over business rules and data integrity at scale |
| n8n as the system of record | n8n is an orchestrator, not a reliable system of record; multi-tenancy and data integrity not suitable |
