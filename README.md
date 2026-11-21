# laravel-words-puzzle-game
Laravel based Words Puzzle Game, in Indonesian language

## Words list format

Word packs live in `storage/app/words-list/*.json`. The file name (without `.json`) is the pack id.

```json
{
    "name": "Nama Pack",
    "words": {
        "8c1f4e2a-5b6d-4c3e-9f10-2a3b4c5d6e7f": {
            "word": "STARDEW VALLEY",
            "hints": ["Hint pertama (paling samar)", "Hint kedua"]
        }
    }
}
```

- Each key in `words` is a unique id for that sentence (a UUID, or any unique string). JSON silently keeps only the last entry when a key is repeated, so make sure ids stay unique.
- `hints` are revealed in order, one per click on **Show Hint**. Each reveal costs the current player `GAME_HINT_PENALTY` points (default 7). Hints stay locked until `GAME_HINT_UNLOCKS_AFTER_TURN` turns of the round have passed (default 5). Both live in `config/game.php`. Leave `hints` as `[]` for no hints.
- If you use `php artisan config:cache`, run it again after changing `config/game.php` or these `.env` values.
- The old format (`"words": ["KALIMAT", ...]`) still loads, with no hints.
- Packs with invalid JSON or no valid sentences are skipped and logged in `storage/logs`.
