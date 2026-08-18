# 19th Episcopal District AdminDash - Task List

## Structured Execution Plan

This file contains the prioritized, actionable tasks for the AdminDash project. Each task includes a clear description, acceptance criteria, priority, and status.

| ID | Title | Priority | Status |
|---|---:|:---:|:---|
| TASK-001 | Public site: match AdminDash index UI + charts | High | pending |
| TASK-002 | Full code review & findings report | High | pending |
| TASK-003 | Implement high-priority fixes from review | Medium | pending |
## Additional: 
1. continue to add more styles to the public website where we display the summaries. As in the Admindash, they have outlines with coloulour and have icons next to the text etc. Take a look on how the index.php of the Admindash is and do the same fpor the public index.php
2. In the Event attendance.php, take a look at the Event Attence breakdown table. Lets have a cloumn to edit or delete a breakdown we have created. Create the necessary form / page with validation .
3. Check the add_media.php. Uploading is failing as it cannot create a folder. Check whatis the error.

4. Take a look at the codebase. Check for any errors or bad programming styles or techniques. See if there were better ways to make this code base more readable debuggable and understanable etc. Check if there are good principles being follwed. COnduct a Full code Review
---

## Task Details

### TASK-001 — Public site: match AdminDash index UI + charts
- Description: Update the public-facing index (public_website) to use the same UI/UX, Bootstrap layout, icons, and the dashboard charts (Chart.js) used by AdminDash/index.php. Ensure responsive behavior, accessible markup, and that the charts pull the same summarized data or safe read-only aggregates.
- Acceptance criteria:
  - Public index visuals match AdminDash index (fonts, colors, icons, layout) for desktop and mobile.
  - Chart components render with identical data summaries (read-only) or safe aggregates.
  - No admin-only functionality (edit, export, login links) is exposed on the public site.
  - Cross-browser smoke test passes (Chrome, Firefox).
- Estimated effort: 3–6 hours

### TASK-002 — Full code review & findings report
- Description: Perform a thorough code review of AdminDash (security, maintainability, DB usage, input validation, CSRF/session handling), and produce a prioritized findings document with remediation steps and estimates.
- Acceptance criteria:
  - A findings report with categorized issues (security, bugs, style, performance), severity, and recommended fixes.
  - A short implementation plan for the top 3 high-severity items.
- Estimated effort: 4–8 hours

### TASK-003 — Implement high-priority fixes from review
- Description: Implement the top-priority fixes from TASK-002 (e.g., hardening auth, input sanitization, fixing SQL usage). Create small, testable commits per fix.
- Acceptance criteria:
  - Each implemented fix includes a brief test/verification step recorded in the repo or PR comments.
  - No regressions introduced in member exports or core admin flows.
- Estimated effort: variable (start with highest-priority item, ~2–4 hours)

---

If this structure looks good, proceed with TASK-001: apply UI and chart parity to the public index and run a quick verification.