# Deploying and launching The Birthday Mystery

## 1. Pre-launch test results (run on the dev machine)

| Check | How it was verified | Result |
|---|---|---|
| Welcome page displays correctly | Phone-size browser; no horizontal overflow | Pass |
| All 11 levels load | Opened and played each one in the browser | Pass |
| Across / Down selection | Tap clue and cell; tap-again toggles direction | Pass |
| Intersections correct | `PuzzleContentTest`: every letter run is a listed word, grids connected | Pass |
| Correct answers accepted | All 11 solved through the real UI | Pass |
| Incorrect answers rejected | Wrong word turned red and nothing unlocked (browser + tests) | Pass |
| Gift reveals at the right time | Only after the server confirms the level; tests cover early access | Pass |
| Locked levels inaccessible | URL jumping, bad numbers and direct POSTs refused | Pass |
| Progress survives refresh | Solved words restored with browser storage cleared | Pass |
| Gift images | The game uses emoji; no image files are configured yet | n/a |
| Finale only after level 11 | Redirects until all 11 are done; opened after level 11 | Pass |
| Android Chrome | Emulated at 375px with a phone user agent. **Not tested on a real phone.** | Emulated only |
| Desktop | Reveal page checked at desktop width | Pass |
| Animations don't block play | Confetti is click-through and honours "reduce motion" | Pass |

Automated: `php artisan test` (38 tests).

**Do this before sending the link:** open the live site on a real Android phone and play level 1.

## 2. Hosting

Any PHP 8.3+ host with MySQL works (shared cPanel hosting, a VPS, Laravel Forge, etc.).
Requirements: PHP 8.3+ with `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `json`; MySQL or MariaDB; HTTPS.

## 3. Deploy steps

1. **Build assets locally** (many shared hosts have no Node):
   ```bash
   npm ci
   npm run build
   ```
   Upload the generated `public/build/` folder along with the code. It is not stored in git.
2. **Upload the project** (git clone, or upload the files). Point the domain's document root at the `public/` folder.
3. **Install PHP dependencies** on the server:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
4. **Create the database** and a MySQL user for it.
5. **Create `.env`** from `.env.production.example` and fill in the database details and `APP_URL`
   (the real `https://` address, no trailing slash).
6. **Set up the app:**
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
7. Make `storage/` and `bootstrap/cache/` writable by the web server.
8. **Get the private link** and open it once on your own device to check it works:
   ```bash
   php artisan game:link
   ```
   Send this link to Tamara, and only to her. It is the only way into the game.

## 4. Day-to-day

- **Never run `migrate:fresh`** on the live site: it wipes her progress.
- Re-running `php artisan db:seed --force` is safe. It refreshes the wording (gift messages, final message)
  but keeps the access token and her progress. After changing text in `config/game.php` run
  `php artisan config:clear && php artisan config:cache`.
- If the link leaks, run `php artisan game:link --rotate` and send the new link. Old links and sessions stop working.
- To reset her progress (for a final test), then the link keeps working:
  ```sql
  UPDATE game_progress SET completed_levels='[]', revealed_gifts='[]', solved_words=NULL;
  ```
  Do this after your own test run and before sending the link.

## 5. Launch-day checklist

- [ ] `APP_DEBUG=false`, `APP_ENV=production`, `SESSION_SECURE_COOKIE=true`
- [ ] Site loads over HTTPS
- [ ] Wording replaced: `config/game.php`, gift messages and final message in `BirthdayGameSeeder.php`
- [ ] Opening the site without the link shows a 404
- [ ] Private link opened on a real phone; level 1 played
- [ ] Progress reset to empty (see above)
- [ ] Link sent to Tamara
