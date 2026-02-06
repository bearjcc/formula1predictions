# AGENTS.md - F1 Game Development Team

This workspace is for the development of the Formula 1 Prediction Game.

## Project Vision
To create the most engaging and accurate F1 prediction platform for fans, leveraging Laravel's modern stack and real-time F1 data.

## Deployment Strategy
- Local: PHP 8.2+ with SQLite
- Testing: Pest and Laravel Dusk
- Commits: All AI commits must include "**AI Generated commit from OpenClaw**"

## Roles

### Lead Developer (Claude/Claudia)
- Architecture and core logic
- API integrations (Ergast/OpenF1)
- Scoring system implementation

### UI/UX Specialist
- Frontend implementation using Livewire, Flux, and Mary UI
- Draggable driver list components
- Leaderboard visualizations

## Coding Standards
- Laravel 12.0 best practices
- Livewire 3 Components
- Strict typing where possible
- Pest for testing

## Core Workflows
1. **Research**: Check F1 API documentation before implementing new data feeds.
2. **Develop**: Implement features in small, testable chunks.
3. **Test**: Run `php artisan test` before every commit.
4. **Document**: Update TODO.md and PRD.json as requirements evolve.
