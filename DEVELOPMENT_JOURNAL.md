# Development Journal

## 2026-04-19

### Tasks Completed

- Reviewed the page structure and final game flow
- Mapped the screen flow directly to PHP pages in the repository
- Implemented shared bootstrap, storage, auth, layout, and game logic includes
- Built registration, login, logout, gameplay, banker, result, and leaderboard pages
- Added responsive CSS and repository documentation
- Added the adaptive strategy prompt feature with `$_SESSION['ai_diff']`, `$_SESSION['seen_ids']`, and `$_SESSION['recent']`

### Testing Notes

- Verified the project uses PHP, HTML, and CSS only
- Verified flat-file storage approach avoids database dependencies
- Verified session-based redirects cover unauthenticated access to protected routes

### Blockers / Risks

- The extra features needed to stay narrow so the core game remained clean and consistent
- The game had to remain clearly single-player while still supporting multiple registered users through the shared leaderboard

### Next Deployment Checks

- Confirm `data/` is writable on the target server
- Test registration, login, full gameplay loop, result page, and leaderboard in a private browser window
