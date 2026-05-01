# Staging Deep Acceptance Request - 2026-05-01

## Metadata

- Project: Mindhikers-Homepage
- Branch: `staging`
- Linear: MIN-167
- Target environment: staging
- Frontend: `https://mindhikers-homepage-staging.up.railway.app`
- WordPress: `https://wordpress-l1ta-staging.up.railway.app`
- Source plan: `docs/plans/2026-05-01_Staging_Deep_Acceptance_Outsourced_Execution_Plan.md`
- Handoff: `docs/dev_logs/HANDOFF.md`

## Goal

Execute the staging deep acceptance checklist from A to G, produce machine-readable evidence, fix only explicitly allowed small issues, and deliver a final report that lets Lao Lu decide whether and how to promote staging toward production.

## Preconditions

Before running the checklist, the executor must verify:

1. Current branch is `staging`.
2. Required CLI tools are available: Node, pnpm, curl, jq, git.
3. Railway access is available for build and runtime logs.
4. WordPress REST credentials are available through environment variables.
5. Revalidate token is available through environment variables.
6. Lighthouse is available, or the C1 fallback path is recorded.

If any mandatory credential is missing, do not improvise. Comment on MIN-167, record the blocker in `docs/testing_reports/escalations.md`, and pause only the blocked group.

## Execution Order

1. Run the self-check in the source plan section 4.1.
2. Fix RED-1 first: `/robots.txt` must return robots text and disallow staging.
3. Execute A: functional pages and routes.
4. Execute B: frontend and WordPress data flow.
5. Execute C: performance and resource baseline.
6. Execute D: SEO and metadata.
7. Execute E: security and headers.
8. Execute F: CMS operation chain through WP REST API.
9. Execute G: deployment stability.
10. Write the final report, daily log, handoff, and MIN-167 summary comment.

## Evidence Requirements

All evidence must be text or JSON unless the source plan explicitly says otherwise.

- Report: `docs/testing_reports/2026-05-01_staging_acceptance_report.md`
- Artifacts: `docs/testing_artifacts/2026-05-01_staging/`
- Status: `docs/testing_reports/status/2026-05-01_staging_acceptance_status.json`
- Escalations: `docs/testing_reports/escalations.md`
- Daily log: `docs/dev_logs/2026-05-01.md`

Each A-G item must include:

1. Status: `PASS`, `WARN`, `FAIL`, or `BLOCKED`
2. Command or API used
3. Evidence file path
4. Notes on any accepted warning
5. Commit hash if a fix was made

## Fix Policy

Fix immediately only when all are true:

1. The issue is listed as fixable in the source plan.
2. The change is under 30 lines.
3. The change touches no more than 3 files.
4. The change does not affect `main`, production, Railway service variables, secrets, or WordPress plugin behavior.

Every fix commit must include:

```text
refs MIN-167
```

Do not push, merge, or touch production without Lao Lu's explicit confirmation.

## Escalation Rules

Escalate through MIN-167 and `docs/testing_reports/escalations.md` when:

1. A fix is more than 30 lines.
2. A fix crosses module boundaries.
3. Build fails twice.
4. The plan does not say how to handle the issue.
5. The issue may affect data safety, secrets, production, or `main`.
6. Required credentials are missing.

Use the escalation template from source plan section 10.2.

## Acceptance Completion

The request is complete only when:

1. A-G results are all reported.
2. RED-1 is fixed and verified.
3. G3 has a clear decision.
4. The report, artifacts, status, daily log, and handoff exist.
5. All fix commits are pushed to `origin/staging`.
6. Railway staging build is `SUCCESS`.
7. MIN-167 has the final summary comment.

## Production Boundary

Production promotion is out of scope for the executor. The final report may recommend one of the source plan's production options, but must not execute it.
