# Project Report - Features, Challenges, and Solutions

## Section 1 - Features Built

- Login system
  Owner: Hafeez Imran
  Sprint: 2
  Notes: Registration, login, logout, session guards, password hashing, remembered username cookie.

- Session tracking and game state
  Owner: Hafeez Imran
  Sprint: 2-4
  Notes: Shared bootstrap, active game state, case map, opened history, offer history, and result-save guard.

- Deal Or No Deal gameplay
  Owner: Hafeez Imran
  Sprint: 3-4
  Notes: 26 briefcases, personal case selection, round schedule, banker page, deal/no-deal resolution.

- Leaderboard
  Owner: Hafeez Imran
  Sprint: 5
  Notes: Flat-file JSON storage, sorting, public leaderboard page.

- Adaptive strategy prompt feature
  Owner: Hafeez Imran
  Sprint: 5
  Notes: Question bank tagged with difficulty and category, `$_SESSION['ai_diff']`, `$_SESSION['seen_ids']`, `$_SESSION['recent']`, and banker-page difficulty indicator.

- Responsive UI and documentation
  Owner: Zerubbabel Ashenafi
  Sprint: 5-6
  Notes: Shared responsive stylesheet, consistent page styling, README, development journal, Scrum notes, and report writing.

## Section 2 - Challenges Faced

1. Deployment-safe routing
   The initial page links were written as root-relative paths such as `/game.php`. That works at the domain root but breaks when the project is hosted inside a CODD subdirectory.

2. Duplicate leaderboard submissions
   Refreshing a completed result page can easily write the same score more than once if the result handler always appends to storage.

3. Scope control
   The project needed to stay clearly single-player while still adding meaningful extras without cluttering the main Deal or No Deal flow.

## Section 3 - Solutions and Lessons Learned

1. Deployment-safe routing fix
   Added `app_base_path()` and `app_url()` helpers in `includes/bootstrap.php`, then updated navigation, form actions, and redirects to use those helpers. Lesson learned: PHP projects intended for CODD should avoid assuming domain-root deployment.

2. Duplicate leaderboard write fix
   Added a `result_saved` flag inside the session game state and only write the leaderboard entry the first time `finalize_game()` runs. Lesson learned: server-side idempotency matters any time a user can refresh a page after a write.

3. Scope-control fix
   Kept the main game loop focused on a single-player case-opening flow, then placed the adaptive strategy prompt on banker rounds so it added depth without changing the show structure. Lesson learned: extra features work better when they support the core loop instead of competing with it.

## Reflection

If starting over, the team would lock the final feature list earlier and prototype the extra systems sooner. That would make the finishing pass more about polish and less about narrowing scope late in development.
