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
- [Project Constitution](docs/PROJECT_CONSTITUTION.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Roadmap](docs/ROADMAP.md)
- [Project Rules](docs/PROJECT_RULES.md)
- [Decisions](docs/DECISIONS.md)

The master blueprint is captured in [`BLUEPRINT.md`](BLUEPRINT.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

Released under the [MIT License](LICENSE).
