---
paths:
  - 'app/Console/**'
---

# Console

## Console commands: thin, scheduled in routes/console.php
- A command parses input, calls an Action (or dispatches a Job), and prints the result. No business logic, no queries in `handle()`.
- Typed `$signature` with explicit options; validate input before acting; return `self::SUCCESS`/`self::FAILURE`.
- Output with Laravel Prompts / `$this->components` helpers; never `echo`. Anything long-running reports progress.
- Schedule in `routes/console.php` with `Schedule::command(...)`, never a `Kernel` class (Laravel 12+ layout). Always add `->withoutOverlapping()` and either `->onOneServer()` or an explicit reason not to.
- A scheduled task that does real work dispatches a queued Job — do not run minutes of work inside the scheduler process.
- Destructive commands are prohibited in production (Essentials `ProhibitDestructiveCommands`); guard your own destructive command with a confirmation and `--force`.
- Test a command with `$this->artisan('name')->assertSuccessful()` plus assertions on the Action's effect or a `Queue::fake()` push.
