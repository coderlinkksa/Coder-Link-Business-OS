# ADR-004 — GitHub as the Single Source of Truth

**Status:** Accepted
**Date:** 2026-06-28

---

## Context

Coder Link Business OS spans multiple components: a Laravel application, n8n workflow
definitions, architecture documents, specifications, and decision records. The team
needs a single, authoritative location for all of these assets that provides change
history, a review process, and a clear record of what is deployed and why.

---

## Decision

GitHub is the single source of truth for all version-controlled assets in Coder Link
Business OS.

This includes: all application code, all n8n workflow files (exported and committed),
all project documentation, all specification files, and all architecture decision records.

No change to production happens without a traceable commit. No secrets, credentials,
or environment-specific configuration are stored in GitHub.

---

## Consequences

**Positive**
- Every change is traceable to a commit, author, and date.
- Pull request workflow enables review before changes reach production.
- Workflow files treated as code prevents undocumented production edits.
- Documentation lives alongside code in one place.
- Free for private repositories at the team's scale.

**Negative**
- Requires the team to commit workflow files after editing in n8n UI (discipline required).
- Engineers must be trained not to commit secrets or sensitive configuration.

---

## Alternatives Considered

| Alternative | Reason Not Chosen |
|-------------|-------------------|
| GitLab (self-hosted) | Additional infrastructure to maintain; GitHub sufficient for current team size |
| Bitbucket | No meaningful advantage; team more familiar with GitHub |
| No version control for workflow files | Creates undocumented production state; unacceptable for a system meant to be maintainable |
| Notion / Confluence for documentation | Separates docs from code; GitHub keeps everything in one auditable place |
