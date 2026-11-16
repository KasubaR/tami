# The Birthday Mystery – Implementation Plan

A one-time, mobile-first birthday crossword site for Tamara, built with Laravel.
11 crosswords → 11 gifts → one final birthday message.
**Out of scope:** admin panel, user registration, general gaming features.

Repo: https://github.com/KasubaR/tami.git
Environment found: PHP 8.5, Composer 2.10, Node 24, XAMPP (MySQL via `C:\xampp\mysql`).

---

## Phase 1 – Project setup and foundation
**Deliverable:** working Laravel app with the visual foundation in place.

1. `composer create-project laravel/laravel .` in this folder (no starter kit, so no auth scaffolding).
2. `git init`, add remote `https://github.com/KasubaR/tami.git`, first commit, push to `main`.
3. Start MySQL in XAMPP, create database `birthday_mystery`; set `DB_*` in `.env`; run `php artisan migrate`.
4. `.env` / `.env.example`: `APP_NAME="The Birthday Mystery"`, timezone, `SESSION_DRIVER=database`.
5. Tailwind CSS via Vite (`npm i -D tailwindcss @tailwindcss/vite`), import in `resources/css/app.css`.
6. Design system in `app.css` (`@theme`): champagne gold / blush / ivory palette, display + body fonts, button, card and grid-cell styles, mobile-first spacing.
7. Create the folder structure:
   - `layouts/app.blade.php`, `welcome.blade.php`
   - `game/dashboard`, `crossword`, `gift-reveal`, `finale` (.blade.php)
   - `components/game-header`, `progress-bar`, `gift-card` (.blade.php)
   - `js/app.js`, `js/crossword.js`
8. Layout: viewport meta, fonts, `@vite`, header slot, footer.
9. Smoke test: `npm run dev` + `php artisan serve`, check on a phone-width viewport.

## Phase 2 – Data model and content
1. Migrations:
   - `puzzles`: id, number (1–11), title, grid (JSON), clues (JSON), solution (JSON), gift_title, gift_message, gift_image.
   - `progress`: one row per puzzle (puzzle_id, completed_at). No users table needed, since there is one player.
2. Seeder: 11 puzzles with grids, clues and gift details (content supplied by you).
3. Model `Puzzle` with a `Progress` relationship.
4. Decide how the player is identified. Recommendation: a single shared progress row tied to the browser session, plus an optional shared passphrase on the welcome page so only Tamara gets in.

## Phase 3 – Welcome page and access
1. Welcome page: title, tagline ("Eleven puzzles. Eleven surprises. One unforgettable birthday"), champagne bottle illustration, "Begin" button.
2. Optional passphrase gate (middleware checking session flag).
3. Routes: `/`, `/play`, `/play/{puzzle}`, `/play/{puzzle}/gift`, `/finale`.

## Phase 4 – Dashboard
1. Show the 11 puzzles as cards: locked / available / completed.
2. Unlock sequentially, so puzzle N+1 opens when N is done.
3. `progress-bar` component (n of 11) and `game-header` component.
4. `gift-card` component showing the gift for completed puzzles.

## Phase 5 – Crossword engine
1. Render grid from JSON in `crossword.blade.php`.
2. `crossword.js`: cell selection, across/down toggle, typing, backspace, arrow-key navigation, clue highlighting, auto-advance.
3. Mobile support: on-screen-keyboard-friendly hidden input, tap-to-toggle direction, large tap targets.
4. Check answer: POST letters to the server. Validate server-side so solutions are never exposed in page source. Return correct/incorrect cells.
5. Save partial progress (localStorage, and optionally server) so a refresh doesn't wipe the grid.
6. Optional hint button (reveal one letter).

## Phase 6 – Gift reveal
1. On a correct solution, mark the puzzle complete and redirect to `gift-reveal.blade.php`.
2. Reveal animation (unwrap / confetti), gift title, message and image.
3. Buttons: back to dashboard / next puzzle.
4. Gift revisit: completed gifts stay viewable from the dashboard.

## Phase 7 – Finale
1. `finale` route only accessible when all 11 puzzles are complete (otherwise redirect).
2. Special birthday message page with champagne animation, fireworks/confetti, and the final message.
3. Replay option: view all gifts again.

## Phase 8 – Polish
1. Animations and transitions (CSS plus small JS), reduced-motion support.
2. Responsive QA at 360px, 390px, 768px, and desktop; iOS Safari and Android Chrome.
3. Accessibility: labels, focus states, contrast.
4. Error and 404 pages in the same theme, favicon, meta/OG tags.

## Phase 9 – Testing
1. Feature tests: access gate, sequential unlocking, answer validation, finale lock.
2. Unit test for the answer checker.
3. Manual playthrough of all 11 puzzles end to end.

## Phase 10 – Deployment
1. Choose hosting (shared host, VPS, or Laravel Forge/Render). Production MySQL.
2. `npm run build`, `php artisan config:cache route:cache view:cache`, `APP_DEBUG=false`.
3. HTTPS, set `APP_KEY`, run migrations and seeders.
4. Final run-through on Tamara's likely device before birthday day.

---

## Open questions
- Puzzle content: do you have the 11 words/clues and gifts ready, or should placeholder content be created first?
- Passphrase gate: wanted, or is an unlisted URL enough?
- Should progress persist across devices, or is browser-session progress fine?
