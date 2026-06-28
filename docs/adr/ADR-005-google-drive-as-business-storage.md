# ADR-005 — Google Drive as Business File Storage

**Status:** Accepted
**Date:** 2026-06-28

---

## Context

Coder Link Business OS generates and manages business files: proposal documents,
signed contracts, project deliverables, and media assets. These files need to be
stored in a location that is accessible to the team, shareable with customers when
needed, and organised consistently without requiring a custom file management system.

---

## Decision

Google Drive is the business file storage layer for all customer-facing and internal
business documents.

Google Drive is responsible for storing proposal documents, signed contracts, project
deliverables, and media assets. A per-customer folder structure is created automatically
by n8n when a company record is created in Laravel. File metadata — name, Drive link,
status — is recorded in the Laravel database. The file content lives in Drive.

---

## Consequences

**Positive**
- Already in use by the team; no new tool to adopt.
- Familiar interface for all team members and for customers receiving shared files.
- No additional infrastructure or hosting cost.
- Google Drive API allows n8n to create folders and upload files automatically.
- Files are accessible from any device without VPN.

**Negative**
- Files are stored in a third-party cloud service; data residency is subject to Google's policies.
- Drive is not a structured database; metadata must be mirrored in Laravel for querying.
- Google account dependency; service disruption would affect file access.

---

## Alternatives Considered

| Alternative | Reason Not Chosen |
|-------------|-------------------|
| Self-hosted file server (e.g. Nextcloud) | Additional infrastructure to host and maintain |
| AWS S3 | Suitable for media/backups, but not a team-friendly document collaboration tool |
| Dropbox | Less integrated with Google Workspace tools already in use |
| Storing files in the database | Not appropriate for large files; does not provide sharing or collaboration |
