# AGENTS.md

## Project Overview

This is a PHP library/project managed with Composer.

The project should be developed with a focus on:
- clean architecture;
- object-oriented design;
- maintainability;
- testability;
- backwards compatibility;
- explicit dependencies;
- minimal and focused changes.

---

## PHP

- Target PHP version: PHP 8.5.
- Use strict typing in PHP files:

```php
<?php

declare(strict_types=1);
```

- Prefer modern PHP 8.x language features when they improve clarity.
- Use typed properties, parameters, and return types.
- Avoid unnecessary `mixed`, untyped properties, and weak typing.
- Prefer immutable objects where appropriate.
- Use `readonly` where it clearly expresses immutability.
- Do not introduce language features incompatible with the project's declared PHP version.

---

## Coding Style

Follow PSR-12 coding standards.

General rules:

- Use meaningful class, method, and variable names.
- Keep methods small and focused.
- Avoid unnecessary nesting.
- Prefer early returns when they improve readability.
- Avoid duplicated logic.
- Do not use global state unless explicitly required.
- Do not add comments that merely repeat what the code does.
- Add comments only when they explain non-obvious decisions or constraints.

---

## Architecture

Respect the existing architecture before introducing new abstractions.

Before making architectural changes:

1. Inspect the existing structure.
2. Identify dependencies and usage.
3. Check existing tests.
4. Determine whether the requested change can be implemented without architectural changes.
5. Prefer the smallest change that solves the problem.

Do not introduce:
- unnecessary design patterns;
- unnecessary interfaces;
- unnecessary abstractions;
- service locators;
- global state;
- static dependencies;

unless there is a clear architectural reason.

When an existing abstraction already solves the problem, reuse it.

---

## Public API

Treat the following as potentially public API:

- public classes;
- public methods;
- public properties;
- constructors;
- interfaces;
- exceptions;
- public constants;
- documented behavior.

Do not break the public API unless explicitly requested.

Before changing a public API:

1. Search for usages.
2. Check tests.
3. Explain the potential impact.
4. Ask for confirmation if the change is potentially breaking.

Prefer backwards-compatible solutions.

---

## Composer

The project uses Composer.

Respect:
- `composer.json`
- `composer.lock`
- PSR-4 autoloading
- existing package dependencies

Do not add a dependency when the existing PHP standard library or an existing project dependency can reasonably solve the problem.

Before adding a new Composer dependency, explain:
- why it is needed;
- why existing dependencies are insufficient;
- what impact it has on the project.

Do not modify `composer.lock` unnecessarily.

---

## Testing

The project uses PHPUnit.

Every bug fix should include a regression test when practical.

When modifying existing behavior:

1. Find the relevant tests.
2. Update or add tests.
3. Run the relevant PHPUnit tests.
4. Run the broader test suite when appropriate.

Do not remove or weaken tests merely to make them pass.

Tests should verify behavior rather than implementation details whenever possible.

---

## PHPStan

The project may use PHPStan for static analysis.

When PHPStan configuration exists:

- respect the existing configuration;
- do not lower the analysis level just to make errors disappear;
- do not add unnecessary ignores;
- fix the underlying type problem whenever practical.

After significant changes, run PHPStan if it is available in the project.

---

## Debugging

When fixing a bug:

1. Reproduce or understand the failure.
2. Identify the root cause.
3. Explain the root cause.
4. Implement the smallest reasonable fix.
5. Add a regression test.
6. Run the relevant tests.
7. Run static analysis when appropriate.

Do not blindly modify multiple unrelated files.

---

## Refactoring

Separate refactoring from behavior changes whenever possible.

If a task requires both:

1. Understand the existing behavior.
2. Preserve existing behavior.
3. Refactor.
4. Add/update tests.
5. Make the required behavior change.
6. Verify the complete test suite.

Avoid large unrelated refactoring during a bug fix.

---

## Git

Keep changes focused.

Before modifying code, inspect the current Git state when relevant.

Do not:
- reset user changes;
- delete uncommitted work;
- rewrite history;
- force-push;
- modify unrelated files;

unless explicitly requested.

Do not assume that every existing change was created by you.

When finishing a task, provide:
- changed files;
- summary of changes;
- tests executed;
- static analysis executed;
- any remaining concerns.

---

## File Safety

Do not modify files that are unrelated to the requested task.

Be especially careful with:
- `.env`
- credentials
- secrets
- deployment configuration
- CI/CD configuration
- migrations
- production configuration

Do not expose secrets in output.

Do not commit credentials, tokens, API keys, passwords, or private keys.

---

## Database and Migrations

Treat database migrations as potentially destructive.

Do not:
- delete migrations;
- rewrite existing migrations;
- drop tables;
- drop columns;
- modify production data;

without explicit authorization.

Prefer creating a new migration when schema evolution is required.

---

## Dependencies

Prefer dependency injection over hard-coded dependencies.

Follow the project's existing dependency injection approach.

Do not introduce a new dependency injection container or framework when an existing project mechanism is already available.

---

## Documentation

Update documentation when a change affects:
- public API;
- installation;
- configuration;
- usage;
- architecture;
- developer workflow.

Do not generate unnecessary documentation for trivial internal changes.

---

## Pull Requests

When preparing a pull request:
- keep the PR focused;
- describe the problem;
- describe the solution;
- mention important design decisions;
- list tests;
- mention known limitations.

Do not include unrelated changes.

---

## Communication

Before making significant changes:
- explain what you intend to change;
- identify potentially risky changes;
- distinguish facts from assumptions.

For small, obvious changes, avoid unnecessary discussion.

When uncertain about architecture or intended behavior, inspect the repository before making assumptions.

Never compensate for uncertainty by making broad unrelated changes.

---

## Change Strategy

Prefer this order:

1. Understand.
2. Search.
3. Plan.
4. Make the smallest appropriate change.
5. Test.
6. Run static analysis.
7. Review the diff.
8. Report the result.

For large architectural changes, use Plan mode before implementation.

---

## Final Response

After completing a task, summarize:

### Changes
- List modified files.
- Explain the important changes.

### Tests
- List PHPUnit commands/tests executed.
- Report the result.

### Static Analysis
- Report PHPStan status if executed.

### Notes
- Mention remaining issues, assumptions, or risks.

Do not claim that tests or commands were executed unless they were actually executed.
