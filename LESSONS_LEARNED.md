# Lessons Learned

Cross-session knowledge for AI agents. Add brief entries when you discover recurring pitfalls or better patterns.

**Format:** Date | Context | Lesson

---

## Entries

<!-- Example entries below; replace with real learnings as they occur -->

- 2025-02-05 | Initial setup | Livewire components require a single root element; use `wire:key` in loops.
- 2026-02-05 | Scoring architecture | Keep all scoring/accuracy logic in `ScoringService` and have models delegate rather than duplicating logic, to avoid drift and untested edge cases.
