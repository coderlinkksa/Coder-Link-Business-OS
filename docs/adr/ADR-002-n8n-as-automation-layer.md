# ADR-002 — n8n as the Automation and Integration Layer

**Status:** Accepted
**Date:** 2026-06-28

---

## Context

Coder Link Business OS requires a layer that handles automation, scheduled jobs,
external integrations, and notifications without embedding that complexity inside
the core Laravel application. The automation layer must be operable and maintainable
by team members who are not software engineers, and must connect to external tools
such as Gmail, Google Drive, and WhatsApp.

---

## Decision

n8n (self-hosted) is the automation and integration layer.

n8n is responsible for listening to events from Laravel, triggering follow-up actions,
sending notifications, interacting with Google Drive and Gmail, running scheduled
workflows, and executing AI automation flows.

n8n is stateless. It does not own business data. All durable state lives in Laravel.
n8n workflow definitions are exported and version-controlled in GitHub.

---

## Consequences

**Positive**
- Visual workflow builder that non-engineers can read and understand.
- Large library of pre-built connectors reduces custom integration code.
- Self-hosted keeps data under Coder Link's control.
- Treating workflows as files enables code review and version control.
- Decouples automation logic from the core application.

**Negative**
- A separate service to host, monitor, and maintain.
- Complex workflows can become difficult to debug.
- Must discipline the team to export and commit workflow files rather than editing only in the UI.

---

## Alternatives Considered

| Alternative | Reason Not Chosen |
|-------------|-------------------|
| Zapier | Cloud-only, limited control, cost scales poorly, not self-hostable |
| Make (Integromat) | Similar limitations to Zapier; data residency concerns |
| Custom Laravel jobs for all automation | Increases application complexity; requires engineering for every new automation |
| Temporal / Airflow | Too complex for a small team; engineering overhead not justified at this stage |
