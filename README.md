# Deal Or No Deal - PHP

Deal Or No Deal - PHP is a browser-based game show built with PHP, HTML, and CSS. Players register and log in, choose a personal briefcase, open cases round by round, review banker offers, accept or reject those offers, reveal the final outcome, and save the result to a leaderboard.

## Team

- Hafeez Imran - Backend lead, authentication, session architecture, game logic, banker offer flow, leaderboard integration
- Zerubbabel Ashenafi - Frontend/UI lead, responsive styling, QA, documentation, Scrum tracking

## Features Built

- Registration and login with PHP form validation, sticky form values, and password hashing
- Session-protected gameplay routes with logout and clean session destruction
- Fully server-side Deal or No Deal gameplay using shuffled briefcase values and round-based opening rules
- Banker offer algorithm with a dedicated negotiation page
- Leaderboard persistence using flat JSON files instead of a database
- Responsive HTML5/CSS3 layout with a shared stylesheet and consistent visual design

## Highlights

- Banker intelligence panel that summarizes volatility, risk band, and offer trends
- Remembered username cookie for returning users
- Adaptive strategy prompt system with changing difficulty on the banker page

## File Structure

- `index.php` - landing page and overview
- `register.php` - registration form and validation
- `login.php` - login form and session creation
- `logout.php` - logout and session teardown
- `game.php` - briefcase board and round progression
- `banker.php` - banker offer review and deal/no-deal decision
- `result.php` - final reveal, analytics, leaderboard write
- `SCRUM_BOARD.md` - sprint backlog with Backlog / In Progress / Review / Done states
- `STANDUP_NOTES.md` - concise daily standup evidence
- `leaderboard.php` - public top-score view
- `includes/` - shared PHP helpers for bootstrap, auth, storage, layout, and game logic
- `styles/main.css` - responsive stylesheet
- `data/` - flat-file storage directory for `users.json` and `leaderboard.json`

## Setup Instructions

1. Place the project folder on a PHP-enabled web server such as the GSU CODD server.
2. Make sure the `data/` directory is writable by PHP so the app can create `users.json` and `leaderboard.json`.
3. Access `index.php` through the server URL, not by opening the file directly in the browser.
4. Register at least one user account, log in, and start a new game from `game.php`.

## Deployment Notes

- The app uses relative base-path helpers so it can run inside a CODD subdirectory without hard-coded root paths.
- Before publishing, test the live URL in an incognito browser window and confirm registration, login, gameplay, banker offers, results, and leaderboard all work.

## Usage Guide

1. Open `index.php`.
2. Register a new contestant account or log in with an existing one.
3. Choose one personal briefcase.
4. Open the required number of cases for the current round.
5. Review the banker offer and select `Deal` or `No Deal`.
6. Finish the run and review the result page.
7. Visit the leaderboard to confirm the score was saved.

## Scrum / Process Evidence

- Sprint summary: `SPRINT_LOG.md`
- Development journal: `DEVELOPMENT_JOURNAL.md`
- Project report draft: `PROJECT_REPORT.md`

## AI Usage Disclosure

This repository includes AI-assisted drafting and implementation support. AI was used to help transform the approved proposal and saved assignment webpages into a PHP project structure, shared helper functions, page templates, styling, and repository documentation. Final file selection, requirement mapping, and implementation review were directed and validated against the proposal, topic page, and grading rubric.

## Source / Assignment References

- `Project_2_Proposal.docx`
- `Project 2 _ PHP-HTML-CSS _ CSC 4370_6370.html`
- `Project 2 Topics 🌸 _ PHP Games _ CSC 4370_6370.html`
- `Project 2 Grading Rubric - CSC 4370_6370.html`
