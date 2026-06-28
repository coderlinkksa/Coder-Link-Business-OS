# ADR-006 — Domain Separation Between Business Logic and Automation

**Status:** Accepted
**Date:** 2026-06-28

---

## Context

Early business systems often start with automation tools handling both business rules
and integrations, leading to a fragile architecture where logic is scattered across
workflows, difficult to test, and impossible to audit. Coder Link Business OS must
avoid this pattern from the beginning.

The system has two distinct concerns that must remain separated:
1. **Business logic and data ownership** — what the business does and what it knows.
2. **Automation and integration** — how the business communicates with external tools and reacts to events.

---

## Decision

Business logic and data ownership belong exclusively to the Laravel application.
Automation and integration belong exclusively to n8n.

The boundary is enforced as follows:
- Laravel is the system of record. It owns all business entities, enforces all rules, and exposes internal APIs.
- n8n is stateless. It reacts to events, calls external services, and writes results back through Laravel's APIs.
- n8n workflows never connect to the database directly.
- Laravel does not call external APIs directly (except where a native Laravel integration is clearly justified and documented).
- No business rule logic lives inside an n8n workflow.

---

## Consequences

**Positive**
- Business rules can be tested independently of automation.
- n8n workflows can be replaced or rebuilt without changing business logic.
- The system of record is always Laravel; there is no ambiguity about where truth lives.
- Debugging is simpler: data problems are investigated in Laravel; integration problems are investigated in n8n.
- The architecture can scale each layer independently.

**Negative**
- Requires discipline to keep the boundary clean as the system grows.
- Some tasks that could be done quickly inside a workflow must instead go through a Laravel API call.

---

## Alternatives Considered

| Alternative | Reason Not Chosen |
|-------------|-------------------|
| n8n handles both logic and integration | Creates an untestable, unauditable system; n8n is not designed to be a system of record |
| All automation inside Laravel jobs | Increases application complexity; limits the team's ability to build automations without engineering |
| No formal boundary; decide case by case | Leads to inconsistent architecture; difficult to onboard new team members or reason about the system |
