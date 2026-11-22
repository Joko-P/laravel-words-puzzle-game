# Tebak Kalimat (Guess the Phrase)

A Laravel 13 (PHP 8.3+) party game in Indonesian, a bit like hangman. **One game master runs it on one screen**: players sit together, say what they want to do, and the game master clicks it in. Players take turns guessing a letter or the whole phrase, and can buy hints. It is not online multiplayer.

UI text and code comments are in **Indonesian**. Keep new UI strings and comments in Indonesian too.

## Run it

```powershell
composer install
php artisan serve
```

- **Never run `php artisan migrate`, `db:seed` or similar without asking the user first.**, the game doesn't need any tables (see State below).
- Read the real `.env`, not `.env.example`, before saying how anything is configured. The real one uses `SESSION_DRIVER=file`, `CACHE_STORE=file`, `LOG_CHANNEL=daily` (logs go to `storage/logs/laravel-YYYY-MM-DD.log`) and `SESSION_LIFETIME=300`.
- **The user doesn't keep a config cache.** They run `php artisan config:clear`, so Laravel reads `config/` directly. If `bootstrap/cache/config.php` exists, it's stale: settings missing from it read as null. `MainService` has no fallback defaults (the user's choice), so a stale cache makes hints cost 0 and unlock from turn 1. When a config change "doesn't apply", check for that file first.
- There are no real tests (only Laravel's example tests). To verify changes, run `php artisan serve`, then drive the flow with `curl` (with a cookie jar) and/or the browser pane.

## Branches

The game code is on `main_local`. `main` only has the README. Work branches are merged into `main_local` with a normal `git merge`: `main_local` already has merge commits, so `--ff-only` fails.

## State: everything lives in the Laravel session

No database models are used. All game data is in the session:

- `gameSessions.<namaSesi>`: one entry per game. Keys:
  - `namaSesi`, `urlEncodedNamaSesi`
  - `sessionCreatedAt`, `startTime`, `endTime`
  - `playersList`: `[id => {id, name, profilePicID, profilePicPath, score, order}]`
  - `loadedWordsList`: shuffled word entries for the game, `{id, packId, word, hints}`. `desc` and `image` are deliberately left out to keep the session small; the game over summary looks them up in the pack files.
  - `roundsAmount`, `currentRound`
  - `turnTimeLimit`: seconds per turn set in the lobby (0 = no limit)
  - `roundsData`: `[roundNumber => round]`
- Each round:
  - `roundNumber`, `packId`, `wordId`, `currentWord` (a plain string). Word ids are only unique *within* a pack, so `packId` is needed to find the entry again.
  - `turnLog`: `[{turn, playerId, type: letter|sentence|hint|timeout, value, correct, points}]`, one entry per action, added through `appendTurnLog()`. It's used for the game over summary. Rounds from before this field existed don't have it.
  - `hints`, `hintsRevealed`, `hintLog`: `[{turn, playerId, penalty}]`
  - `availableLetters`, `guessedLetters`
  - `currentPlayerTurn` (a player's `order`), `turnCount` (the current turn number within the round, starting at 1)
  - `turnStartedAt` (epoch milliseconds; reset whenever the turn passes), `timeoutLog`: `[{turn, playerId, penalty}]`
  - `isAnswered`, `roundWinner`
- `current_game`: `{name, url}`. This is **per browser, not per tab**: running two games in two tabs doesn't work.

**Session names are used as dot-notation session keys** (`'gameSessions.' . $name`), so they must match `^[A-Za-z0-9_\-]+$`. That rule is validated in `MainController::start_new_game`. A dot in a name splits it into nested keys and causes 500s.

## Code map

- `routes/web.php`: menu routes, plus the `game/{urlEncodedNamaSesi}/…` group behind the `activeGameSession` middleware (`app/Http/Middleware/ActiveGameSession.php`). The middleware compares the URL parameter with `current_game`'s stored URL-encoded name.
- `app/Http/Controllers/MainController.php`: create, continue and delete sessions, session details, random avatar.
- `app/Http/Controllers/GameController.php`: lobby, players, start, rounds, guesses, hints, game over.
  - Use `currentSessionKey()` / `currentSession()` for the session path.
  - `gameAlreadyStarted()` guards the endpoints: players can't be changed after Start, and the game screen and guesses are refused before it.
  - `new_round_data()` builds a round; `make_new_rounds()` advances rounds and sets `endTime` once.
  - `game_screen_data()` strips `loadedWordsList` and unrevealed `hints` before sending data to the browser.
  - `applyTurnTimeoutIfExpired()` is the only place a timeout happens. It runs at the start of `game_screen_data()`, both guess handlers, `show_hint()` and `turn_timeout()`. It applies at most one timeout per call and does nothing before the time is up, so calling it repeatedly is safe.
  - **Any new code that passes the turn must also reset `turnStartedAt`**, or the next player inherits the old clock. **Any new action must also call `appendTurnLog()`**, or it won't show in the summary.
  - `game_over_screen()` builds the summary with `buildGameSummary()`. It loads `WordsList::all()` once and calls `WordsList::wordInfo()` for each round's `desc`/`image`. If there's no `packId` (older games), it falls back to matching the word text.
- `app/Services/WordsList.php`: loads and normalises word packs (see below).
- `app/Services/Emoji.php`: OpenMoji avatars from `public/profile-pics-openmoji/`. In the lobby, the "Change" button opens a picker modal (`#profilePicModal`, every entry of `Emoji::all()`). The default avatar is still random (`get_random_profile_pic()`, an `emojiDeck` in the session) on page load and after each Tambah Player.
- `app/Services/MainService.php`: helpers.
  - `returnAllLettersAsArray()` returns the guessable characters, A–Z and 0–9. Each round copies it into `availableLetters`.
  - `hintPenalty()`, `hintUnlocksAfterTurn()` and `turnTimeoutPenalty()` read config with no fallback; the defaults live only in `config/game.php`.
- `config/game.php`: `hint_penalty` (`GAME_HINT_PENALTY`, default 5), `hint_unlocks_after_turn` (`GAME_HINT_UNLOCKS_AFTER_TURN`, default 5) and `turn_timeout_penalty` (`GAME_TURN_TIMEOUT_PENALTY`, default 35).
- Views in `resources/views/`: `layouts/main-layout.blade.php`, with header, rules and credits modals, and `@inject('mainService', …)`. Plus `game-views/{main-menu,lobby,game-screen,game-over-screen}.blade.php`. The front end is Bootstrap 5.3, jQuery 4 and SweetAlert2. Tailwind/Vite are installed but not used.

## Game rules (keep the rules modal in sync)

- **Letter guess** (a letter A–Z or a digit 0–9; the game screen keyboard has a number row above QWERTY):
  - correct: +25 + round(100 / number of times the character appears);
  - wrong: −25;
  - the turn always passes to the next player.
- **Sentence guess:**
  - correct: +100 + 20 × letters still hidden, and the round ends;
  - wrong: −20 × letters still hidden, and the turn passes.
- **Revealing the last letter** also ends the round. Whoever does it wins the round.
- **Hints:**
  - revealed in order from a word's `hints`, and they cost the current player `hint_penalty` points;
  - the player keeps their turn;
  - locked while `turnCount <= hint_unlocks_after_turn`;
  - at most **one hint per turn**, checked with `hintLog`.

  Each rule is enforced in `show_hint()` on the server. The button state in JS only reflects it.
- **Turn time limit** (optional, set under "Pengaturan Lainnya" in the lobby, 0–600 s, 0 = off):
  - the clock keeps running during the hint and sentence prompts;
  - when it runs out, the current player loses `turn_timeout_penalty` points and the turn passes, which counts as a turn for the hint lock;
  - a guess or hint that arrives after the time is up is refused;
  - the bar in JS counts down from the server's `remaining_ms` (so the PC's clock doesn't matter), but the server decides when time is up.
- **Rounds:** at least 2 players, 1–16 rounds, capped at the number of words available.

## Word packs: `storage/app/words-list/*.json`

The file name is the pack id.

```json
{ "name": "Pack Name", "words": { "<uuid>": { "word": "STARDEW VALLEY", "hints": ["vaguest", "...", "most obvious"] } } }
```

- `word` uses only **A–Z, 0–9 and spaces**, because no other character can be guessed. `WordsList::normalizeEntry()` enforces this with `^[A-Z0-9 ]+$`, *after* uppercasing and collapsing spaces, so lowercase input is fine. Any other entry is skipped and logged. Each part must be ≤ 16 characters, because the game screen wraps rows at 16 columns.
- Digits and spelled-out numbers are different answers: `5 CM` doesn't match a guess of `LIMA CM`. Only use digits where they're really part of the name.
- Hints are written in Indonesian, from vaguest to most obvious, and could contain any part of the answer.
- "Could contain any part of the answer" means, if the word is `PANTAI SOMETHING SOMETHING`, and the hint is `Pantai di daerah something`, then it's allowed. Just, keep it lax. Not the whole asnwer in the hint, but not too strict either.
- **Keys must be unique.** `json_decode` silently keeps only the last of a duplicate key.
- Optional `desc` and `image` fields are shown only in the game over summary.
  - `desc` goes through `WordsList::formatDescription()`. It escapes everything, then restores only `<b> <strong> <i> <em> <u> <s> <del> <br>` and `<a href='http(s)://…'>`, with either quote style and `target="_blank" rel="noopener noreferrer"`. It's the only HTML rendered with `{!! !!}` on that page, so keep it that way.
  - `image` goes through `WordsList::imageUrl()`. It must match `images/words-images/…` with an image extension (jpg, jpeg, jfif, png, webp, gif, svg; `.jfif` is just JPEG, and the user's images use it) and exist under `public/`; otherwise it's ignored. The suggested layout is `public/images/words-images/<pack id>/<word id>.<ext>`.
- The old format (`"words": ["…"]`) still loads, with generated ids and no hints.
- Invalid packs or entries are skipped and logged as warnings instead of causing a 500.
- `popular-actors-pack.json` (95 names) was a first draft written by Claude. Its hints still need a fact check by the user.

## Conventions and gotchas

- **Validate on the server, not only in JS.** The UI can be stale (Back button, cached pages, two tabs), so every rule needs a server-side check. Return 4xx JSON instead of letting a 500 happen.
- `Collection::firstWhere('order', $x)` compares loosely, so `null == 0` is true. Use strict comparisons for `order`.
- **`"0"` is falsy in PHP.** Since digits are guessable, never check guess input with `if ($letter)` / `if ($sentence)`; use `!== ''`. Use strict `in_array(..., true)`, `array_search(..., true)` and `===` for letters. (In JS, `"0"` is truthy, so `if (letter)` there is fine.)
- **Layout:** the original desktop layout (1920×1080 at 100% zoom) must stay as it is.
  - `.app-shell` is exactly one screen tall (`100vh` / `100dvh`), so the page itself never scrolls. The header and footer never shrink and are always visible. Only `.main-content-area` scrolls (`min-height: 0; overflow-y: auto`) when content doesn't fit. Inside it, `.main-content-inner` is centred with auto margins, so tall content scrolls instead of being clipped at the top. To scroll in JS, use `.main-content-area`, not `window`.
  - The page title is `#header-screen-title` (currently an `<h4>`). Select it by id only; the game screen JS rewrites it with "Round X, Turn Y".
  - The game screen's letter boxes and keys shrink to fit.
  - The bottom row is 2/4/1/5 columns from `md` up, and stacks below that.
  - Don't use `vw-100` or `100vw` widths: they cause sideways scrolling.
- In new JS, put user and pack text into the page with `.text()`, not into HTML strings. Hints already do this. Older code still builds HTML strings with player names, which are limited to `[\w\- ]` by validation.
