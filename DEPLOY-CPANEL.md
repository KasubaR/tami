# Deploying to cPanel shared hosting (no command line needed)

**This deployment:** subdomain `tami.sunconect.com`, database `suncyfal_birth`, database user `suncyfal_tami`.
The host already runs other Laravel apps, so this app lives in its own folder (`birthday-mystery`) and the
subdomain's document root points only at that folder's `public/`. Nothing else is touched.
The package's settings file is already filled in with these details (the password is not stored in git).

Everything below happens in your host's cPanel. You need three things from the `deploy/` folder
(created by the packaging step, kept out of git):

| File | What it is |
|---|---|
| `birthday-mystery-upload.zip` | The whole app, ready to run (PHP libraries and built front-end included) |
| `birthday-mystery-database.sql` | The empty game database: 11 puzzles, gifts, and the private token |
| `PRIVATE-link-token.txt` | Your secret token. **Keep this on your computer. Do not upload it.** |

## Before you start

- Your host must offer **PHP 8.4 or newer** (cPanel → *MultiPHP Manager* or *Select PHP Version*).
  The app will not start on older versions.
- Needed PHP extensions (all normally on by default): `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `fileinfo`, `tokenizer`, `xml`.
- Your domain must be added to the account and have an SSL certificate (cPanel → *SSL/TLS Status* → *Run AutoSSL*).

## Steps

### 1. Create the database
*(Already done for this deployment: `suncyfal_birth` with user `suncyfal_tami`. Skip to step 2 if the user has All Privileges on it.)*

cPanel → **MySQL® Databases**
1. Create a database (for example `birthday`). cPanel adds your account prefix, giving `youracct_birthday`.
2. Create a database user with a strong password.
3. **Add the user to the database** with *All Privileges*.
4. Write down the full database name, full username and the password.

### 2. Import the game data
cPanel → **phpMyAdmin** → click your new database in the left list → **Import** →
choose `birthday-mystery-database.sql` → **Import**. You should see 14 tables appear.

### 3. Upload the app
cPanel → **File Manager**
1. Go to your **home folder** (the level *above* `public_html`). Do not upload into `public_html`.
2. **Upload** `birthday-mystery-upload.zip`, right-click it → **Extract**.
   You should now have a folder `birthday-mystery` containing `app`, `public`, `vendor`, and so on.

### 4. Point the subdomain at the `public` folder
cPanel → **Domains** → find `tami.sunconect.com` (create it first if it doesn't exist) → *Manage* → **Document Root** →
set it to `birthday-mystery/public` → Save. (If you create the subdomain from the *Subdomains* screen instead, type
`birthday-mystery/public` as its document root.)

If your host does not let you change the document root, ask their support, or use this fallback:
move everything *inside* `birthday-mystery/public` into `public_html`, then edit `public_html/index.php`
and change the two `__DIR__.'/../` paths to `__DIR__.'/../birthday-mystery/`.

### 5. Create the settings file
In File Manager, turn on **Settings → Show Hidden Files (dotfiles)**.
1. In `birthday-mystery`, rename `env-rename-to-.env.txt` to `.env`.
2. It is already filled in for `tami.sunconect.com` and the `suncyfal_birth` database, so no edits are needed.
   If you ever change host or database, edit `APP_URL` and the `DB_*` lines (use the full prefixed names).
3. `APP_KEY` is already filled in. Do not change it. Do not copy another app's `.env` over it.

### 6. Set the PHP version and permissions
- cPanel → *MultiPHP Manager* → tick only `tami.sunconect.com` → **PHP 8.4** (or newer) → Apply.
  This setting is per domain, so your other Laravel apps keep their own PHP version.
- File Manager: set `birthday-mystery/storage` and `birthday-mystery/bootstrap/cache` to permission **775**
  (right-click → *Change Permissions*, include sub-folders).

### 7. Check it works
1. Open `https://tami.sunconect.com/`. You should see a plain **404 Not Found**. That is correct:
   the game is private and invisible without the link.
2. Open `https://tami.sunconect.com/g/` followed by the token from `PRIVATE-link-token.txt`.
   You should land on the welcome page.
3. Play level 1 **on a real Android phone**.

### 8. Reset progress and send the link
Your test plays are saved. Before sending the link, in phpMyAdmin → your database → **SQL**, run:

```sql
UPDATE game_progress SET completed_levels='[]', revealed_gifts='[]', solved_words=NULL;
```

Then send Tamara `https://tami.sunconect.com/g/<token>`.

## Changing the wording later
The personal messages are in the app files, so after editing them locally, re-package and re-upload, or edit
the files directly in File Manager:

- `config/game.php`: welcome intro, how it works, closing message.
- Gift messages and the final message: live in the database. Edit them in phpMyAdmin
  (tables `gifts` → `reveal_message`, and `birthday_games` → `birthday_message`).

## If the link leaks
In phpMyAdmin run the following with a new random 40-character letters/digits value. The old link stops working immediately.

```sql
UPDATE birthday_games SET access_token='PASTE-A-NEW-RANDOM-40-CHARACTER-VALUE';
```

## Troubleshooting

| Symptom | Fix |
|---|---|
| Plain "500" or blank page | Open `birthday-mystery/storage/logs/laravel.log`. Usually the PHP version (needs 8.4+), permissions, or a wrong `.env` value. |
| "Your host must offer PHP..." / Composer platform error | PHP version is too old for this domain. Set 8.4+. |
| "No application encryption key" | `.env` is missing or still named `env-rename-to-.env.txt`. |
| Database connection refused | Check the full prefixed DB name and username; try `DB_HOST=localhost`. |
| Every page is 404, even with the link | The document root is not `birthday-mystery/public`, or the token is wrong. |
| Page has no styling | `public/build` is missing; re-upload the zip. |
| Redirect loop or mixed-content warnings | Make sure `APP_URL` starts with `https://`. |
