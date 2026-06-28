# ADR-003 — PostgreSQL as the Primary Database

**Status:** Accepted
**Date:** 2026-06-28

---

## Context

The Laravel application requires a reliable relational database to store all business
entities: leads, contacts, companies, opportunities, proposals, projects, invoices,
subscriptions, and the audit log. The database must support relational integrity,
structured queries for pipeline reporting, and a clear migration strategy as the
schema evolves.

---

## Decision

PostgreSQL is the primary database for the Coder Link Business OS Laravel application.

The database is accessed exclusively through the Laravel application. n8n and other
tools interact with data through Laravel's internal APIs, not by connecting to the
database directly. All schema changes are managed through versioned Laravel migrations
reviewed and committed to GitHub before being applied.

---

## Consequences

**Positive**
- Mature, proven relational database with strong data integrity guarantees.
- Excellent support within Laravel's Eloquent ORM and query builder.
- Supports future requirements such as JSON columns, full-text search, and row-level security.
- Open source with no licensing cost.
- Straightforward to run in Docker for local development.

**Negative**
- Requires database administration knowledge for tuning and maintenance.
- A single relational database may need to be split if the system scales significantly.

---

## Alternatives Considered

| Alternative | Reason Not Chosen |
|-------------|-------------------|
| MySQL / MariaDB | PostgreSQL preferred for stricter type enforcement and richer feature set |
| SQLite | Not suitable for production multi-user workloads |
| MongoDB | Business data is relational by nature; a document store adds complexity without benefit at this scale |
| PlanetScale | Cloud-managed adds vendor dependency; PostgreSQL preferred for control and portability |
