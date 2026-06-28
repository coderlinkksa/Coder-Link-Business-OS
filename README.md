# AI Marketing OS

A modular, multi-tenant, AI-native marketing automation platform — an operating
system for marketing on top of which capabilities are installed, run, monitored,
and retired like applications.

> **Status:** Foundation. This repository currently contains the project scaffold
> only. Architecture, workflows, database design, and APIs will be added per
> specifications from the project architect.

## Repository Structure

```
ai-marketing-os/
├── docs/            Project documentation (constitution, architecture, roadmap, rules, decisions)
├── control-plane/   System of record: tenants, users/RBAC, config, billing, audit
├── orchestration/   n8n workflows: router, modules, shared sub-workflows
├── ai-assets/       Prompt library and prompt evaluations
├── connectors/      Adapters to external platforms
├── experience/      Admin console and tenant dashboard
└── infrastructure/  Containers, environments, deployment definitions
```

## Documentation

Start with the project documentation in [`docs/`](docs/):

- [Business Model](docs/BUSINESS_MODEL.md)
- [Company Knowledge](docs/COMPANY_KNOWLEDGE.md)
- [Project Constitution](docs/PROJECT_CONSTITUTION.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Roadmap](docs/ROADMAP.md)
- [Project Rules](docs/PROJECT_RULES.md)
- [Decisions](docs/DECISIONS.md)
- [CRM Domain Model](docs/CRM_DOMAIN_MODEL.md)
- [Sales Pipeline](docs/SALES_PIPELINE.md)
- [Service Catalog](docs/SERVICE_CATALOG.md)
- [Technical Architecture](docs/TECHNICAL_ARCHITECTURE.md)

The master blueprint is captured in [`BLUEPRINT.md`](BLUEPRINT.md).

## Architecture Decision Records

Key decisions recorded in [`docs/adr/`](docs/adr/):

- [ADR-001 — Laravel as Core Business Application](docs/adr/ADR-001-laravel-as-core.md)
- [ADR-002 — n8n as Automation Layer](docs/adr/ADR-002-n8n-as-automation-layer.md)
- [ADR-003 — PostgreSQL as Primary Database](docs/adr/ADR-003-postgresql-as-primary-database.md)
- [ADR-004 — GitHub as Source of Truth](docs/adr/ADR-004-github-as-source-of-truth.md)
- [ADR-005 — Google Drive as Business Storage](docs/adr/ADR-005-google-drive-as-business-storage.md)
- [ADR-006 — Domain Separation](docs/adr/ADR-006-domain-separation.md)

## Specifications

- [Lead Capture Workflow](specifications/workflows/LEAD_CAPTURE_SPEC.md)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

Released under the [MIT License](LICENSE).
