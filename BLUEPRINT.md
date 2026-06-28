# AI Marketing OS — Master Blueprint

> **Status:** Foundational / Ratified
> **Document type:** Architectural Constitution
> **Version:** 1.0
> **Owner:** Founder (non-developer) + Chief Software Architect
> **Last updated:** 2026-06-28

---

## 0. How to Read This Document

This is the **Constitution** of AI Marketing OS. It does not contain code, workflows, schemas, or implementation. It contains *decisions, boundaries, and principles*.

Three rules govern its use:

1. **Every future decision must be traceable to this document.** If a proposed change contradicts the Constitution, either the change is wrong or the Constitution must be formally amended first (see §18, Documentation Strategy → Amendment Process).
2. **When in doubt, prefer the boring, modular, reversible choice.** This system will outlive any single tool, model, or vendor.
3. **The owner is not a developer.** Any design that requires the owner to read code to operate the business is a design defect.

---

## 1. Vision

**To make world-class marketing fully autonomous — so that any business, regardless of size or budget, can operate as if it had an elite in-house marketing department running 24/7.**

AI Marketing OS is not a collection of automations. It is an **operating system for marketing**: a stable kernel on top of which marketing capabilities are installed, run, monitored, and retired like applications. In its mature form, a business owner states a goal ("grow qualified leads in Riyadh by 30% this quarter") and the system plans, executes, measures, and self-corrects across every channel.

---

## 2. Mission

To build a **modular, multi-tenant, AI-native marketing automation platform**, orchestrated by n8n, that:

- Automates the full marketing lifecycle: strategy → planning → creation → publishing → distribution → capture → nurture → measurement → optimization.
- Treats every capability as an independent, replaceable module with a clear contract.
- Keeps a human in control of strategy and approvals while AI does the labor.
- Is operable by a non-developer and maintainable by a small team.
- Scales from one company to thousands of tenants without architectural rewrites.

---

## 3. Project Philosophy

1. **Platform, not project.** We build the kernel and the module system first. Features are modules installed onto the kernel.
2. **Orchestration over implementation.** n8n coordinates; specialized services do the heavy lifting. We avoid reimplementing what mature services already do well.
3. **Contracts over connections.** Modules communicate through stable, versioned contracts (events and data shapes), never through tangled point-to-point wiring.
4. **AI is a capability, not the architecture.** Models change every few months. The system must treat any model as a swappable component behind a stable interface.
5. **Human-in-the-loop by default, autonomous by graduation.** Every capability ships with an approval gate. Autonomy is *earned* per-tenant once trust and metrics justify it.
6. **Observable by construction.** If it isn't logged, traced, and recoverable, it isn't done.
7. **Reversible by default.** Prefer decisions that can be undone. Hard-to-reverse decisions require explicit sign-off.
8. **Boring infrastructure, exciting outcomes.** Use proven, well-understood components so the novelty budget is spent on marketing intelligence, not plumbing.
9. **Tenant data is sacred.** Isolation and privacy are non-negotiable, not features.

---

## 4. High-Level Architecture

The system is organized into **seven layers**. Each layer depends only on the layers below it through defined contracts. No layer reaches across boundaries.

```
┌──────────────────────────────────────────────────────────────────┐
│  L7  EXPERIENCE LAYER                                               │
│      Admin Console · Tenant Dashboard · Approvals · Reporting UI   │
├──────────────────────────────────────────────────────────────────┤
│  L6  CONTROL PLANE (System of Record + Identity + Tenancy)         │
│      Tenant registry · Users/RBAC · Config · Billing · Audit       │
├──────────────────────────────────────────────────────────────────┤
│  L5  ORCHESTRATION LAYER  (n8n)                                     │
│      Router · Module workflows · Sub-workflows · Schedulers        │
├──────────────────────────────────────────────────────────────────┤
│  L4  CAPABILITY MODULES  (the "apps" of the OS)                    │
│      Content · Social · Leads · CRM · Email · WhatsApp · SEO · …   │
├──────────────────────────────────────────────────────────────────┤
│  L3  AI LAYER  (Model Gateway · Prompt Library · RAG · Guardrails) │
├──────────────────────────────────────────────────────────────────┤
│  L2  INTEGRATION LAYER  (Connectors to external services)          │
│      Social APIs · Email/ESP · WhatsApp · Ads · Analytics · Scrapers│
├──────────────────────────────────────────────────────────────────┤
│  L1  DATA & PLATFORM LAYER                                          │
│      Relational DB · Vector DB · Object storage · Cache/Queue ·     │
│      Secrets vault · Observability stack                            │
└──────────────────────────────────────────────────────────────────┘
```

**Architectural style:** A **control plane / data plane split** with **event-driven, modular orchestration**.

- The **Control Plane (L6)** is the brain and system of record. It holds *truth*: who the tenants are, what they're allowed to do, what their configuration is, what's pending approval. It is the only place state lives permanently.
- The **Orchestration Layer (L5, n8n)** is the *muscle*. It is treated as **stateless and disposable**: any single n8n execution can fail and be replayed without corrupting the system, because truth lives in the Control Plane, not inside a running workflow.
- **Capability Modules (L4)** are the *organs* — independent units of marketing function.

**Why this split is the single most important decision:** n8n is an excellent orchestrator but a poor system of record and not natively multi-tenant. By keeping all durable state, identity, and tenancy in a dedicated Control Plane and treating n8n as replaceable execution, we get multi-tenancy, testability, and disaster recovery that n8n alone cannot provide — and we can even swap orchestrators in the future without losing the business.

---

## 5. Main Components

| # | Component | Layer | One-line purpose |
|---|-----------|-------|------------------|
| C1 | **Experience Apps** | L7 | Human surfaces: admin console, tenant dashboard, approval inbox, reports |
| C2 | **Control Plane Service** | L6 | System of record: tenants, users, RBAC, config, billing, audit |
| C3 | **Orchestration Core (n8n)** | L5 | Executes and coordinates all automation |
| C4 | **Router / Dispatcher** | L5 | Single entry point that routes every request/event to the right module |
| C5 | **Capability Modules** | L4 | The marketing "apps" (content, social, leads, CRM, etc.) |
| C6 | **AI Gateway** | L3 | One stable interface to all AI models (text, image, video, embeddings) |
| C7 | **Prompt Library** | L3 | Versioned, governed store of all prompts |
| C8 | **Knowledge / RAG Service** | L3 | Per-tenant brand knowledge and retrieval |
| C9 | **Guardrail Service** | L3 | Safety, brand-voice, policy and PII checks on AI in/out |
| C10 | **Integration Connectors** | L2 | Adapters to each external platform |
| C11 | **Data Stores** | L1 | Relational + vector + object + cache |
| C12 | **Secrets Vault** | L1 | Centralized credential storage |
| C13 | **Observability Stack** | L1 | Logging, metrics, tracing, alerting |
| C14 | **Job/Event Bus** | L1 | Async messaging and scheduling backbone |
| C15 | **Error Recovery Subsystem** | cross | Retries, dead-letter handling, self-healing, escalation |

---

## 6. Responsibilities of Every Component

**C1 — Experience Apps**
- Present dashboards, content calendars, approval queues, and reports.
- Capture human decisions (approve/reject/edit) and push them to the Control Plane.
- Never call modules or external APIs directly; they talk only to the Control Plane.

**C2 — Control Plane Service** *(system of record)*
- Own tenant lifecycle, user identity, roles/permissions, per-tenant configuration and feature flags, subscription/billing state, and the immutable audit log.
- Expose a stable internal API that the Experience Apps and n8n consume.
- Enforce *what a tenant is allowed to do* before any work is dispatched.
- Hold the canonical copies of business entities (campaigns, content items, leads, contacts) — n8n reads/writes through it, not around it.

**C3 — Orchestration Core (n8n)**
- Run all workflows: triggers, schedules, branching, fan-out/fan-in, retries.
- Remain stateless: pull tenant context at the start of a run, write results back, hold nothing durable.
- Be organized into a Router + Module workflows + shared Sub-workflows (see §15).

**C4 — Router / Dispatcher**
- Be the **single front door** for inbound triggers (webhooks, schedules, UI actions, events).
- Resolve tenant identity and authorization (by calling the Control Plane), then dispatch to the correct module workflow with a normalized payload.
- Centralize cross-cutting concerns: auth check, rate-limit check, idempotency, correlation-ID stamping, logging entry/exit.

**C5 — Capability Modules**
- Each module owns one marketing domain end to end.
- Each module exposes a stable contract: defined inputs, defined outputs, defined events emitted.
- Each module is independently versioned, testable, and replaceable.
- A module never reaches into another module's internals — it emits an event or calls a published contract.

**C6 — AI Gateway**
- Provide one interface for *generate text*, *generate image*, *generate video*, *embed*, *transcribe*, regardless of underlying provider.
- Handle model selection/routing, fallback to alternates, retries, cost accounting, token/usage logging, and caching.
- Be the *only* place that holds AI provider credentials and knows provider-specific quirks.

**C7 — Prompt Library**
- Store every prompt as a versioned, named asset with metadata (purpose, owner, model affinity, last-evaluated score).
- Separate prompt *templates* from runtime *variables*; no hardcoded prompts inside workflows.
- Support A/B variants and rollback to prior versions.

**C8 — Knowledge / RAG Service**
- Maintain each tenant's brand knowledge base: brand voice, products, offers, tone, banned words, past winning content.
- Provide retrieval to ground AI outputs in tenant-specific truth (strictly tenant-scoped).

**C9 — Guardrail Service**
- Validate AI inputs and outputs: brand-voice conformance, policy/legal compliance, prohibited content, PII leakage, hallucination/factuality checks where feasible.
- Decide: pass · pass-with-edit · route-to-human · block.

**C10 — Integration Connectors**
- Wrap each external platform (social networks, ESPs, WhatsApp providers, ad platforms, analytics, scrapers) behind a consistent internal adapter.
- Absorb platform-specific auth, pagination, rate limits, and error shapes so modules see a uniform interface.

**C11 — Data Stores**
- Relational DB: structured truth (within the Control Plane boundary).
- Vector DB: embeddings for RAG and semantic search.
- Object storage: generated media (images, video, exports).
- Cache: hot config, rate-limit counters, idempotency keys.

**C12 — Secrets Vault**
- Hold all credentials/API keys centrally, scoped per tenant where applicable, with rotation and least-privilege access. No secret in workflow JSON, code, or env files committed to a repo.

**C13 — Observability Stack**
- Collect structured logs, metrics, and traces with a correlation ID spanning Router → module → AI → connector.
- Power dashboards and alerts; retain history for audit and post-incident review.

**C14 — Job/Event Bus**
- Carry asynchronous work and inter-module events; decouple producers from consumers; enable retries and backpressure.

**C15 — Error Recovery Subsystem**
- Standardize retry policy, dead-letter capture, automatic re-drive, and escalation to humans when automation cannot recover.

---

## 7. System Boundaries

**Inside the system (we own and govern):**
- Control Plane, Router, Module workflows, AI Gateway, Prompt Library, Guardrails, Connectors, Data Stores, Observability, Recovery.

**Outside the system (we depend on, never trust blindly):**
- AI providers (LLMs, image/video models).
- Social platforms, ESPs, WhatsApp Business providers, ad networks, analytics, scrapers.
- Payment/billing providers.

**Boundary rules:**
1. **All outside access goes through a Connector or the AI Gateway.** No module calls an external API directly. This makes every external dependency swappable and individually rate-limitable, retryable, and monitorable.
2. **The Experience Layer never touches L4/L5/L2.** It speaks only to the Control Plane. This keeps the UI thin and the security surface small.
3. **n8n never owns durable business state.** It is allowed scratch/working state only for the duration of an execution.
4. **A module owns its domain but not another module's data.** Cross-module needs are met via events or published contracts.
5. **Tenancy is enforced at the Router and Control Plane**, not inside individual modules — modules trust that context arriving is already authorized, but still scope every query by tenant.

**Explicit non-goals (for v1):**
- We are not building our own LLM, image model, or video model.
- We are not building our own ad-buying engine or our own social network.
- We are not replacing best-in-class niche tools where a connector suffices.

---

## 8. Technology Decisions

Decisions are stated as **roles**, with a primary choice and the principle behind it. Specific products may be swapped as long as the role and contract are preserved.

| Role | Decision | Rationale |
|------|----------|-----------|
| **Orchestration engine** | **n8n** (self-hosted, queue mode for scale) | Visual, maintainable by non-deep-devs, huge connector ecosystem, fits "owner is not a developer." |
| **Control Plane / system of record** | A dedicated application + **PostgreSQL** | Relational integrity for tenants, billing, audit; mature, boring, reliable. (Owner's existing Laravel familiarity is an asset here, but the choice is the *role*, not a framework lock-in.) |
| **Vector store** | A managed/self-hosted vector DB (e.g., pgvector to start) | Start inside Postgres (pgvector) to reduce moving parts; graduate to a dedicated vector DB only when scale demands. |
| **AI model access** | **Provider-agnostic AI Gateway**; default to the strongest available Claude models for reasoning/content, best-in-class specialists for image/video | Never hardcode a model. The Gateway makes models swappable as the market shifts. |
| **Object storage** | S3-compatible storage | Standard, cheap, portable. |
| **Cache / queue** | **Redis** | Powers n8n queue mode, rate limiting, idempotency, caching. |
| **Secrets** | Dedicated secrets manager / vault | Centralized rotation and least-privilege; never secrets in repo. |
| **Observability** | Structured logging + metrics + tracing stack | One correlation ID end-to-end. |
| **Hosting** | Containerized (Docker), orchestratable | Reproducible environments; portable across providers. |
| **External data / scraping** | Managed actor/scraper services via connectors | Don't maintain brittle scrapers in-house. |

**Decision principles:**
- **Manage, don't maintain** anything that isn't our core differentiator.
- **One way to do each thing.** Avoid two databases that do the same job, two queues, two logging formats.
- **Portability beats lock-in.** Prefer open standards and S3/SQL/Redis-style interfaces.

---

## 9. Data Flow

**Canonical request lifecycle (example: "publish this week's content"):**

```
Trigger (schedule / UI action / inbound webhook)
        │
        ▼
   ROUTER  ── resolves tenant, checks auth + rate limit + idempotency, stamps correlation ID
        │
        ▼
 CONTROL PLANE  ── returns tenant config, brand profile, feature flags, approvals state
        │
        ▼
  MODULE (e.g., Content) ── builds task
        │
        ├──► AI GATEWAY ──► (Prompt Library + RAG context) ──► model ──► draft
        │                                   │
        │                            GUARDRAILS  ── pass / edit / human / block
        ▼
  APPROVAL GATE (human or auto, per tenant policy)
        │
        ▼
  CONNECTOR (e.g., Social) ── publishes via external API
        │
        ▼
 CONTROL PLANE ── records result, status, metrics, audit
        │
        ▼
 OBSERVABILITY ── logs/metrics/trace; EVENT BUS emits "content.published"
        │
        ▼
 Downstream modules react (Analytics ingests; Reporting updates)
```

**Data-flow rules:**
1. **Every flow carries a correlation ID** from the Router to the last step — one trace per business action.
2. **State is read at the start and written at the end** of a module run; n8n holds no truth between runs.
3. **All AI generation passes through Gateway → Guardrails** before it can reach a connector or a human.
4. **Nothing reaches an external audience without passing the Approval Gate** (which may be set to auto for graduated tenants).
5. **Every meaningful state change emits an event** so the system is extensible without modifying producers.
6. **PII is minimized in transit** and never logged in clear text.

---

## 10. AI Strategy

**Principle: AI is a swappable capability behind a stable interface. The architecture must never depend on a specific model.**

1. **Model-agnostic Gateway (C6).** All AI calls go through one interface. Swapping or upgrading a model is a Gateway configuration change, not a workflow rewrite. Default to the most capable current Claude models for reasoning and content; route image/video/audio to best-in-class specialists.
2. **Task-appropriate routing.** Cheap/fast models for classification and routing; premium models for high-stakes creative and strategy. The Gateway chooses based on task class, not the workflow author.
3. **Prompt as a governed asset (C7).** No prompt is hardcoded in a workflow. Prompts are versioned, named, owned, and evaluated. This is what keeps quality maintainable by a non-developer over years.
4. **Grounding via RAG (C8).** AI outputs are grounded in per-tenant brand knowledge to reduce generic, off-brand, or hallucinated content. Retrieval is strictly tenant-scoped.
5. **Guardrails on both sides (C9).** Inputs are sanitized (no prompt injection, no PII leakage to providers beyond policy); outputs are checked for brand voice, policy/legal compliance, and safety before release.
6. **Human-in-the-loop, graduating to autonomy.** Every AI action starts behind an approval gate. A tenant earns higher autonomy per capability as quality metrics and trust accumulate. Autonomy levels are explicit: `suggest → approve-each → approve-batch → auto-with-review → full-auto`.
7. **AI as named agents.** "AI Marketing Manager" and other agents are *roles* composed of (a) a system persona prompt, (b) a tool-set (modules they may invoke), and (c) an autonomy level. Agents are configuration, not bespoke code.
8. **Cost and usage are first-class.** Every AI call logs tokens, latency, cost, model, and tenant. Budgets and alerts prevent runaway spend.
9. **Evaluation loop.** Outputs and downstream performance (engagement, conversions) feed back to score prompts and models, driving continuous improvement.

---

## 11. Automation Strategy

1. **Router-first.** One dispatcher is the entry point for all automation. New capabilities register with the Router; they don't invent new front doors.
2. **Modules as independent units.** Each capability is a self-contained workflow set with a contract. Build, test, deploy, and version them independently.
3. **Reusable sub-workflows.** Common operations (call AI, run guardrails, publish to a channel, log an event, handle an error) exist once as shared sub-workflows and are reused everywhere. **No copy-paste logic.**
4. **Event-driven, not point-to-point.** Modules emit events; interested modules subscribe. Adding a consumer never requires editing the producer.
5. **Idempotency everywhere.** Every externally-visible action is safe to retry (idempotency keys), so a replay never double-posts or double-charges.
6. **Schedules + triggers + events.** Three legitimate ways to start work: time-based schedules, external triggers (webhooks), and internal events. All funnel through the Router.
7. **Graceful degradation.** If a non-critical step fails (e.g., one channel API is down), the rest proceeds; the failed unit is queued for recovery, not allowed to fail the whole run.
8. **Everything is observable and recoverable** (see §12 and §15).

---

## 12. Security Strategy

Security is layered (defense in depth). No single control is trusted alone.

1. **Tenant isolation is paramount.**
   - Every record is tenant-scoped; every query filters by tenant.
   - Authorization is enforced at the Router and Control Plane *before* dispatch.
   - Per-tenant credentials are isolated in the vault; one tenant can never use another's connections.
2. **Secrets management (C12).** All credentials in a vault with least-privilege access and rotation. **No secrets in repositories, workflow exports, or plaintext env files.**
3. **Identity & access (RBAC).** Roles for owner, admin, tenant-admin, operator, viewer. Permissions checked centrally. Principle of least privilege.
4. **AI-specific security.** Prompt-injection defense in the Guardrail layer; control over what tenant data is sent to external providers; provider data-retention settings reviewed and documented.
5. **Data protection & privacy.** PII minimized, encrypted at rest and in transit, never logged in clear. Compliance posture explicitly designed for relevant regimes (e.g., Saudi PDPL, GDPR for applicable tenants) — including consent tracking, data-subject requests, and retention limits.
6. **Boundary hardening.** All inbound webhooks authenticated and signature-verified; rate limiting and abuse protection at the Router.
7. **Auditability.** Immutable audit log of who/what/when for every sensitive action (config change, approval, publish, credential use).
8. **Supply-chain caution.** Vet connectors and third-party nodes; pin versions; review before upgrades.
9. **Separation of environments.** Strict separation of dev / staging / production data and credentials.

---

## 13. Scalability Strategy

1. **Stateless orchestration scales horizontally.** Because n8n holds no durable truth, run it in **queue mode** with multiple workers behind Redis; add workers to add throughput.
2. **Control Plane scales independently** from orchestration (separate service, separate database scaling path).
3. **Async by default.** Long or bursty work goes on the Event/Job Bus; producers never block on consumers.
4. **Per-tenant fairness.** Rate limiting and quotas prevent one heavy tenant from starving others ("noisy neighbor" control).
5. **Backpressure & graceful degradation.** When a downstream (AI provider, social API) is saturated, the system queues and retries rather than failing hard.
6. **Caching.** Hot config, brand profiles, and idempotency keys cached to reduce database and API load.
7. **Scale the data tier deliberately.** Start simple (Postgres + pgvector); split out the vector store and add read replicas/partitioning only when metrics justify it. Avoid premature distribution.
8. **Multi-tenancy model that grows.** Begin with a shared, tenant-scoped database (row-level isolation). Reserve schema-per-tenant or dedicated instances for large/enterprise tenants when needed — the contract (everything is tenant-scoped) makes this migration possible without rewrites.
9. **Capacity is measured, not guessed.** Scaling decisions are triggered by observability metrics (queue depth, latency, error rate), not hunches.

---

## 14. Repository Strategy

**Decision: a structured multi-repo (or well-bounded monorepo with clear package boundaries) organized by *concern*, with strict separation of code from configuration from secrets.**

Recommended top-level repositories (or monorepo packages):

1. **`platform-control-plane`** — the Control Plane service + database migrations.
2. **`orchestration-n8n`** — exported workflows (Router, modules, sub-workflows) as version-controlled definitions, organized by module; treated as source, reviewed via pull requests.
3. **`ai-assets`** — the Prompt Library and prompt evaluation assets (versioned, reviewable, diffable).
4. **`experience-apps`** — admin console + tenant dashboard.
5. **`infrastructure`** — containerization, environment definitions, deployment manifests (no secrets).
6. **`docs`** — this Constitution, ADRs, runbooks, contracts.

**Rules:**
- **Workflows are code.** They are exported, version-controlled, peer-reviewed, and promoted through environments — never hand-edited only in production.
- **Prompts are versioned assets**, reviewed like code.
- **Secrets never enter any repo.** Configuration is separated into non-secret (committed) and secret (vaulted).
- **One module = one clear, self-contained area** so it can evolve independently.
- **Every repo has a README and CONTRIBUTING that point back to this Constitution.**

---

## 15. Workflow Strategy (n8n)

The orchestration layer follows a strict, repeating shape so any workflow is predictable to a non-developer.

**The three workflow tiers:**

1. **Router workflow (one).** The single entry point. Responsibilities: authenticate, resolve tenant, authorize, rate-limit, idempotency, stamp correlation ID, dispatch to the correct module, log entry/exit. It contains *no business logic*.
2. **Module workflows (one set per capability).** Each implements one domain (Content, Social, Leads, etc.) using a consistent skeleton: **receive normalized input → load context → do domain work (calling shared sub-workflows) → record result → emit event → return**.
3. **Shared sub-workflows (the standard library).** Reusable building blocks used by all modules:
   - *Call-AI* (wraps the AI Gateway)
   - *Run-Guardrails*
   - *Publish-to-Channel* (wraps a connector)
   - *Log-Event* / *Emit-Event*
   - *Handle-Error* (standard retry/escalate)
   - *Get-Tenant-Context*

**Workflow rules:**
- **Every workflow has one job.** If it does two things, split it.
- **No business logic in the Router; no infrastructure logic in modules** (modules call sub-workflows for AI, publishing, logging, errors).
- **No hardcoded credentials, prompts, or tenant data** inside any workflow.
- **Every workflow is idempotent and retry-safe.**
- **Every workflow logs start, end, and errors** with the correlation ID.
- **Standard error path:** all failures route to *Handle-Error*, never silently swallowed.
- **Naming and folders follow §24 and §25.**

---

## 16. Database Strategy

**Principle: the database is the system of record and lives in the Control Plane. n8n is not a database.**

1. **One relational source of truth (PostgreSQL).** Holds tenants, users/roles, configuration, business entities (campaigns, content items, leads, contacts, approvals), billing, and the audit log.
2. **Tenant scoping is mandatory.** Every business table carries a tenant identifier; every access is filtered by it. This is the backbone of multi-tenancy.
3. **Vector data alongside relational to start (pgvector).** Embeddings for RAG/semantic search begin inside Postgres to minimize moving parts; graduate to a dedicated vector DB only when scale requires.
4. **Object storage for media** (images, video, exports) — the database stores references, not blobs.
5. **Cache for hot/ephemeral data** (Redis) — config, rate-limit counters, idempotency keys. Never the source of truth.
6. **Migrations are versioned and reviewed.** Schema evolves through tracked migrations, never by hand in production.
7. **Separation of concerns in data:** operational data, analytics data, and audit data have clear homes; heavy analytics never contends with live operations.
8. **Backups, retention, and recovery are defined per data class**, including privacy-driven retention limits and deletion on request.
9. **No PII where it isn't needed**; sensitive fields encrypted; access logged.

---

## 17. Prompt Strategy

**Principle: prompts are the source code of the AI behavior. They are governed like code.**

1. **Central Prompt Library (C7).** Every prompt is a named, versioned asset with metadata: purpose, owning capability, intended model class, variables, and last evaluation score.
2. **Templates vs. variables.** Prompts are templates; runtime data is injected as variables. No tenant data baked into a prompt; no prompt baked into a workflow.
3. **Layered prompt composition.** A prompt is assembled from layers: *system/persona* + *task instructions* + *brand/RAG context* + *guardrail constraints* + *user/runtime input*. Each layer is owned and reusable.
4. **Versioning and rollback.** Every change is versioned; any prompt can be rolled back instantly.
5. **A/B and evaluation.** Variants can run in parallel; outcomes (quality scores + downstream performance) decide winners. Underperforming prompts are retired.
6. **Brand voice as data, not prose-in-a-prompt.** Each tenant's voice/tone/banned-words live in the Knowledge Service and are injected, so the same prompt template serves all tenants.
7. **Safety constraints are mandatory layers**, not optional add-ons.
8. **Prompts are documented** so a non-developer understands what each does and why.

---

## 18. Documentation Strategy

**Principle: the system is only as maintainable as its documentation. Docs are part of "done."**

1. **This Constitution is the top of the hierarchy.** Everything traces to it.
2. **Architecture Decision Records (ADRs).** Every significant decision is recorded: context, decision, alternatives, consequences. ADRs are how the Constitution is *amended* — a new ADR can supersede a prior one, with explicit sign-off.
3. **Module contracts.** Each module documents its inputs, outputs, events, and dependencies. This contract is the stable promise other modules rely on.
4. **Runbooks.** For operations: how to deploy, how to recover from common failures, how to onboard a tenant, how to rotate a credential. Written so a non-developer (or future hire) can follow them.
5. **Living diagrams.** The layer diagram and key data flows are kept current.
6. **Glossary.** Shared vocabulary (tenant, module, connector, gateway, autonomy level) so everyone means the same thing.
7. **Docs live with code** in the `docs` repo and module READMEs; outdated docs are treated as bugs.
8. **Amendment process:** to change the Constitution → open an ADR → review → if accepted, update this document with version bump and changelog entry.

---

## 19. Deployment Strategy

1. **Containerized everything.** Every service runs in containers for reproducibility and portability.
2. **Three environments minimum:** development → staging → production, with strict data and credential separation.
3. **Promotion, not editing.** Workflows, prompts, and code are promoted from staging to production through a controlled process — never edited directly in production.
4. **Infrastructure as configuration.** Environments are defined declaratively (no snowflake servers).
5. **Safe rollout.** Prefer gradual rollout and the ability to roll back instantly (versioned workflows/prompts make this possible).
6. **Health checks and readiness.** Every service reports health; the system knows when a component is degraded.
7. **Zero-downtime goal.** Stateless orchestration + external state make rolling updates feasible.
8. **Backups verified, recovery rehearsed.** A backup that hasn't been restored in a drill is not a backup.
9. **Secrets injected at deploy time** from the vault, never baked into images.

---

## 20. Future Expansion Strategy

The architecture is designed so growth is *additive*, not *disruptive*.

1. **New capability = new module.** Register with the Router, implement the standard skeleton, publish a contract. No existing module changes.
2. **New channel = new connector.** Add an adapter behind the Integration Layer; modules use it through the uniform interface.
3. **New model = Gateway config.** Adopt a new/better model without touching workflows.
4. **New tenant tier = isolation upgrade.** Move large tenants from shared to dedicated data without rewriting modules.
5. **Marketplace potential.** Because modules and prompts are governed assets with contracts, a future "module marketplace" or template store is a natural extension.
6. **Agentic expansion.** More autonomous AI agents (campaign strategist, budget optimizer) are added as roles (persona + tools + autonomy), reusing existing modules as their tool-set.
7. **Internationalization & multi-language** are first-class (especially Arabic/English), handled as data, not forks.
8. **White-label / reseller** path is preserved by the strict tenancy and configuration model.

**Expansion guardrail:** *no new feature may bypass the Router, the AI Gateway, or the Connector layer to "save time."* Shortcuts that break boundaries are technical debt that compounds.

---

## 21. Risks

| # | Risk | Impact | Mitigation |
|---|------|--------|------------|
| R1 | **Treating n8n as the whole system** (state, tenancy, logic all inside it) | Becomes unmaintainable, untestable, not multi-tenant | Control plane / data plane split (§4); n8n stateless |
| R2 | **Vendor/model lock-in** (AI or platform) | Costly forced rewrites | AI Gateway + Connector abstraction; portable infra (§8, §10) |
| R3 | **Cross-tenant data leakage** | Catastrophic trust/legal failure | Mandatory tenant scoping; central authz; isolated credentials (§12) |
| R4 | **AI quality/hallucination/off-brand output** | Brand damage, lost trust | Guardrails + RAG grounding + human-in-the-loop graduation (§10) |
| R5 | **Runaway AI cost** | Budget blowout | Per-call cost logging, budgets, alerts, model routing (§10) |
| R6 | **External API fragility** (rate limits, breaking changes, bans) | Broken automations | Connector abstraction, retries, backpressure, recovery subsystem (§11, §15) |
| R7 | **Prompt sprawl / copy-paste logic** | Unmaintainable mess | Prompt Library + shared sub-workflows; no duplication (§15, §17) |
| R8 | **Secrets leakage** | Breach | Vault, no secrets in repos, rotation (§12) |
| R9 | **Owner is non-developer → bus factor / operability gap** | Can't operate or recover | Runbooks, observability, approval surfaces, boring tech (§12, §18) |
| R10 | **Compliance gaps** (PDPL/GDPR) | Legal/financial exposure | Privacy by design, consent, retention, audit (§12, §16) |
| R11 | **Silent failures** | Damage discovered too late | Observable-by-construction; standard error path; alerting (§12, §15) |
| R12 | **Premature scaling / over-engineering** | Wasted effort, fragility | Start simple, scale on metrics (§13) |
| R13 | **Scope explosion** (build everything at once) | Never ships | Phased roadmap, modules one at a time (§26) |

---

## 22. Design Principles

1. **Separation of concerns** — each component does one thing.
2. **Single source of truth** — state lives in exactly one place (Control Plane).
3. **Stateless orchestration** — executions are disposable and replayable.
4. **Contracts over connections** — depend on published interfaces, not internals.
5. **Abstraction at the boundaries** — Gateway for AI, Connectors for everything external.
6. **Idempotency** — every action is safe to retry.
7. **Event-driven extensibility** — add consumers without touching producers.
8. **Human-in-the-loop, autonomy by graduation.**
9. **Observable and recoverable by construction.**
10. **Reversible by default; hard-to-reverse needs sign-off.**
11. **Least privilege everywhere.**
12. **Boring, proven infrastructure; novelty spent on marketing intelligence.**
13. **Don't repeat yourself** — one shared implementation per concern.
14. **Operable by a non-developer** — if it needs code-reading to run, redesign it.

---

## 23. Engineering Standards

1. **Definition of Done includes:** working, tested, observable (logs/metrics/trace), documented (contract + runbook note), idempotent, and tenant-scoped.
2. **Everything is versioned and reviewed** — code, workflows, prompts, schema migrations — via pull requests.
3. **No secrets in source.** Ever.
4. **No hardcoded prompts, credentials, or tenant data in workflows.**
5. **Standard error handling** through the shared Handle-Error path; no swallowed errors.
6. **Standard logging format** with correlation IDs across all layers.
7. **One naming convention, applied everywhere** (§25).
8. **Backward-compatible changes preferred;** breaking a contract requires versioning the contract.
9. **Test before promote;** nothing edited directly in production.
10. **ADR for every significant decision.**
11. **Small, reversible changes** over big-bang rewrites.
12. **Measure before optimizing or scaling.**

---

## 24. Folder Organization Strategy

Organize **by domain/concern first, by type second**, consistently across every repo. Illustrative structure (names indicative, not prescriptive code):

```
ai-marketing-os/
├── docs/
│   ├── BLUEPRINT.md            (this Constitution)
│   ├── adr/                    (architecture decision records)
│   ├── contracts/             (per-module input/output/event contracts)
│   ├── runbooks/
│   └── glossary.md
├── control-plane/
│   ├── domain/                (tenants, users, billing, audit, entities)
│   ├── api/
│   └── migrations/
├── orchestration/
│   ├── router/
│   ├── modules/
│   │   ├── content/
│   │   ├── social/
│   │   ├── leads/
│   │   ├── crm/
│   │   ├── email/
│   │   ├── whatsapp/
│   │   ├── seo/
│   │   └── competitor-analysis/
│   └── shared/                (call-ai, guardrails, publish, log, error)
├── ai-assets/
│   ├── prompts/               (versioned, by capability)
│   └── evaluations/
├── connectors/                (one folder per external platform)
├── experience/
│   ├── admin-console/
│   └── tenant-dashboard/
└── infrastructure/
    ├── containers/
    ├── environments/          (dev / staging / prod, non-secret config)
    └── deployment/
```

**Rules:**
- A capability's everything (workflows, prompts pointer, contract, docs) is discoverable from its module folder.
- Shared building blocks live in exactly one `shared/` location.
- Mirror the same top-level vocabulary across repos so people navigate by intuition.

---

## 25. Naming Strategy

**Principle: names are an interface. Consistent names make a complex system legible to a non-developer.**

1. **Layer/area prefixes** so any artifact announces where it belongs, e.g. `router.*`, `module.content.*`, `shared.call-ai`, `connector.instagram`, `prompt.content.caption.v3`.
2. **Workflows:** `module.<domain>.<action>` (e.g., `module.social.publish-post`). Sub-workflows: `shared.<function>`.
3. **Prompts:** `prompt.<capability>.<purpose>.v<version>`.
4. **Connectors:** `connector.<platform>` with consistent internal operation names (`create`, `read`, `publish`, `metrics`).
5. **Events:** `<entity>.<pastTenseVerb>` (e.g., `content.published`, `lead.qualified`). Past tense = it already happened.
6. **Data entities:** clear singular/plural conventions; always include the tenant scope concept.
7. **Environments:** `dev`, `staging`, `prod` — no creative aliases.
8. **Versions:** explicit `vN` suffixes for anything that can change behavior (prompts, contracts).
9. **No abbreviations that aren't in the glossary.** Clarity beats brevity.
10. **One language for names** (English) even where content is multilingual.

---

## 26. Suggested Development Roadmap

Phased so the platform is *useful early* and *expanded safely*. Each phase ends with something operable and observable. **Do not start a phase until the prior phase's foundation rules are met.**

### Phase 0 — Foundations (the kernel)
*Goal: a skeleton that can safely run one capability for one tenant.*
- Stand up Data & Platform layer (DB, cache, object storage, secrets vault, observability).
- Build the Control Plane minimal core: tenant, user/RBAC, config, audit.
- Build the Router (auth, tenant resolution, dispatch, logging, idempotency).
- Build shared sub-workflows: Call-AI, Guardrails, Log/Emit-Event, Handle-Error, Get-Tenant-Context.
- Stand up the AI Gateway + Prompt Library (even with one model).
- **Exit criteria:** an end-to-end request flows Trigger → Router → a trivial module → AI Gateway → Guardrails → log, fully traced, single-tenant.

### Phase 1 — First vertical: Content
*Goal: prove the module pattern with real value.*
- Content Planning + Content Generation modules.
- Brand Knowledge (RAG) per tenant; approval gate; basic tenant dashboard + approval inbox.
- **Exit criteria:** a tenant plans and generates on-brand content with human approval, fully logged.

### Phase 2 — Publishing & Distribution
- Social Media Publishing (connectors for priority channels).
- Scheduling and the approval-to-publish flow.
- AI Image Generation integrated into the content flow.
- **Exit criteria:** approved content auto-publishes on schedule with recovery on failure.

### Phase 3 — Capture & Convert
- Lead Generation + Lead Qualification + CRM module.
- Email Marketing and WhatsApp Automation modules.
- **Exit criteria:** leads captured, qualified, stored, and nurtured across email/WhatsApp.

### Phase 4 — Measure & Optimize
- Social Media Analytics ingestion; Reporting Dashboard; Workflow Monitoring.
- Error Recovery subsystem hardened; cost/usage dashboards.
- **Exit criteria:** the owner sees performance and system health in one place; failures self-heal or escalate.

### Phase 5 — Intelligence
- AI Marketing Manager agent (persona + tools + autonomy) orchestrating modules.
- SEO Automation; Competitor Analysis.
- Graduated autonomy enabled per tenant/capability.
- **Exit criteria:** the system proposes and (with permission) executes cross-channel plans.

### Phase 6 — Scale & Multi-Tenant SaaS hardening
- n8n queue mode + workers; per-tenant quotas/rate limits.
- Billing/subscription; onboarding automation; white-label readiness.
- Compliance hardening (PDPL/GDPR), backup/recovery drills.
- **Exit criteria:** onboard a new tenant self-service; scale workers on metrics; pass a recovery drill.

### Phase 7+ — Expansion
- Additional channels, models, agents, and a potential module/template marketplace — all additive, all through the existing boundaries.

**Roadmap discipline:** ship one module fully (built + tested + observable + documented) before starting the next. Breadth without depth creates fragile demos, not a platform.

---

## Appendix A — The Ten Inviolable Rules (quick reference)

1. State lives in the Control Plane. **n8n is stateless.**
2. Every external call goes through a **Connector** or the **AI Gateway**.
3. Every request enters through the **Router**.
4. Everything is **tenant-scoped**.
5. **No secrets, prompts, or tenant data hardcoded** in workflows.
6. Every action is **idempotent** and **logged with a correlation ID**.
7. AI output passes **Guardrails** before reaching humans or channels.
8. Nothing reaches an audience without passing the **Approval Gate** (auto only by graduation).
9. New capability = **new module**; new channel = **new connector**; new model = **Gateway config**. Never bypass a boundary to save time.
10. If it isn't **observable, recoverable, and documented**, it isn't done.

---

*End of Constitution v1.0. Amend only via ADR with version bump (see §18).*
