# Contributing

Thank you for contributing to AI Marketing OS.

## Ground Rules

- Every change must trace back to the [Project Constitution](docs/PROJECT_CONSTITUTION.md).
  If a change contradicts it, either the change is wrong or the Constitution must
  be formally amended first via the [Decisions log](docs/DECISIONS.md).
- **No secrets in the repository.** Never commit credentials, API keys, or `.env`
  files. Use `.env.example` to document required variables.
- Prefer small, reversible changes over large, hard-to-undo ones.
- Keep the repository clean: no dead code, no commented-out blocks, no unused files.

## Workflow

1. Create a branch for your change.
2. Make focused commits with clear messages (see below).
3. Open a pull request for review.
4. Ensure documentation is updated alongside code.

## Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) style:

```
<type>: <short summary>
```

Common types: `feat`, `fix`, `chore`, `docs`, `refactor`, `test`.

## Documentation

Documentation is part of "done." Update the relevant files in [`docs/`](docs/)
when behavior or structure changes. Outdated documentation is treated as a bug.
