# Status

GPL fork of Honeypot for Contact Form 7 2.1.1 (Nocean), renamed Classic Honeypot for CF7 and free of ads. Milestones 1 to 5 are finished and merged into `main`. Milestone 5 set up the development environment (Playground with a blueprint, a Studio site linked to the repo and reachable from the local network, MCP Adapter connected to Claude Code), which will serve as the basis for a public skill. Local folder and GitHub repo renamed to the slug `classic-honeypot-for-cf7`.

**Blocked:** waiting for the manual review by the WordPress.org plugin team (resubmitted for the third time on 2026-10-14; the reviewer handling the appeal said he will continue the review himself).

**Next step:** wait for the manual review and address whatever they ask by replying to their email (do not resubmit unless they ask); if approved, upload the code to trunk, create the 1.0.0 tag and upload `assets/` to SVN.

## Log

- 2026-10-16: migrated to the new Guppy format (STATUS.json).

- 2026-10-14: Another reviewer closed the submission without seeing the appeal notes (second rejection). When asked, the appeal reviewer asked us to upload it again and let him know in the thread so he can continue the review himself. Resubmitted.

- 2026-10-13: Appeal accepted for consideration: WordPress.org asked us to upload the zip again from the submission form and said a reviewer will get in touch. Resubmitted. Waiting for the manual review.

- 2026-10-12: WordPress.org rejects it as a duplicate of Honeypot for Contact Form 7. Appealed by replying to their email with `classic-honeypot-for-cf7-1.0.0.zip`: it is a direct copy of 2.1.1 (May 2021, the last Nocean release), the original was bought in July 2024, and since 2.1.1 it is an independent parallel development with no code from later changes. We also explained why the `[honeypot]` tag and the `wpcf7_honeypot_*` filters are kept. Waiting for a reply.

- 2026-10-11: Submitted to WordPress.org (1.0.0 zip from `312c621`). Initial slug `classic-honeypot-for-cf7`. Automated scan: Pass. The note to reviewers explains the fork and why the `[honeypot]` tag and the `wpcf7_honeypot_*` filters are kept.

- 2026-10-11: New icon (assets and settings screen at 64x64), screenshots retaken with it, and 772x250 and 1544x500 banners generated from `assets-src/` (HTML + script, excluded from the zip). `assets/` complete. Plugin ready to submit.

- 2026-10-11: Preparing the release (branch `feature/release-assets`): deleted `languages/` (and the Domain Path header), created `assets/` for the WordPress.org SVN (excluded from the zip) with the two readme screenshots and icon and banner placeholders. Final icon and banner design still pending.

- 2026-10-11: Branch `docs/readme` published on GitHub for Nilo's review: readme.txt rewritten in a neutral tone (independent fork developed in parallel since 2.1.1, no criticism of the original) and a new README.md (excluded from the zip). Plugin Check: 0 errors, 0 warnings (the `wpcf7_honeypot_*` filters are kept for compatibility with a justified `phpcs:ignore`; the WordPress.org review may ask to change them). Review of the original's fixes up to 3.7.2: everything applicable is already included.

- 2026-10-11: Branch `fix/settings-menu`: the settings page shows up again in the Contact menu as "Honeypot settings", at the end (CF7 6.2 changed the menu slug to `wpcf7-dashboard` and had left it hidden). Pending Nilo's review.

- 2026-10-11: Decided not to add abilities (Abilities API) to the plugin. Remaining: Nilo's review, minor changes (icon) and publishing on WordPress.org.

- 2026-10-11: Milestone 5 closed: MCP tested from a new Claude Code session; `feature/dev-environment` merged into `main`.

- 2026-10-11: Milestone 5, step 3: Studio site `classic-honeypot.local` with the plugin linked to the repo (junction), CF7 and a test page; reachable from the local network by adding the machine's IP to the hosts file. MCP Adapter 0.7.0 connected to Claude Code over STDIO (`studio wp mcp-adapter serve`, local scope, no application password). Playground also reachable on the local network with `--site-url`.

- 2026-10-11: GitHub repo renamed to `nilovelez/classic-honeypot-for-cf7`; remote and Plugin URI updated.
- 2026-10-11: Local folder renamed to `classic-honeypot-for-cf7` (the slug); Claude Code memory migrated and verified.
- 2026-10-11: Milestone 5, step 2: development blueprint.json; plugin tested in Playground (honeypot, time check and settings import, all correct).
- 2026-10-11: guppy/STATUS.md created.
