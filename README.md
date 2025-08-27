# Formula 1 Predictions App

A Laravel 11 web application for F1 race predictions, evolved from a manual spreadsheet system (2022-2024). Built with LiveWire 3, Tailwind CSS, and real-time notifications.

## 📋 Quick Links
- [Status](#status)
- [Features](#features)
- [Installation](#installation)
- [Scoring](#scoring)

## 📊 Status

### ✅ **Completed**
- **Core Infrastructure**: Laravel 11 + LiveWire 3 + Tailwind CSS
- **Database Schema**: Complete F1 models (drivers, teams, circuits, races, predictions, standings)
- **Real-Time Notifications**: WebSocket system with email fallback (18 tests passing)
- **Prediction System**: Drag-and-drop driver ordering with fastest lap selection
- **User Authentication**: Laravel's built-in auth system
- **Testing Framework**: 100% test pass rate across 28+ test files

### 🚨 **Critical Next Steps**
- [ ] **Automated Scoring**: Implement comprehensive scoring algorithm with DNF/edge cases
- [ ] **Deadline Management**: Automated enforcement and reminder system
- [ ] **Driver Substitutions**: Smart prediction replacement for mid-season changes
- [ ] **Mobile Optimization**: Touch-friendly interface for all devices

### 🔧 **High Priority**
- [ ] **Leaderboard System**: Real-time standings with historical tracking
- [ ] **Superlatives System**: Preseason/midseason prediction types
- [ ] **Bot Integration**: AI-powered competitive predictions
- [ ] **Historical Data Import**: Migration from spreadsheet data

## 🚀 Features

**Core**: Race predictions (1-20 + fastest lap), preseason/midseason superlatives, real-time scoring
**UX**: Drag-and-drop interface, mobile-responsive, real-time notifications
**Competitive**: Leaderboards, bot predictions, achievement system, head-to-head comparisons

## ⚙️ Installation

```bash
git clone <repo> && cd formula1predictions
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## 🎯 Scoring System

| Position Difference | Points |
|-------------------|--------|
| Correct | +25 |
| 1 away | +18 |
| 2 away | +15 |
| 3 away | +12 |
| 4 away | +10 |
| 5 away | +8 |
| 6 away | +6 |
| 7 away | +4 |
| 8 away | +2 |
| 9 away | +1 |
| 10+ away | -1 to -25 |
| Correct DNF | +10 |
| Incorrect DNF | -10 |
| Perfect bonus | +50 |

**Historical Benchmarks**: Top performers 250-275 pts/race, average 220-250 pts/race, random baseline ~116.5 pts/race

## 🔌 API Integration

Uses [F1 API](https://f1api.dev/docs/sdk) for real-time race data, driver info, and standings.

## 🧪 Testing

```bash
php artisan test                    # All tests
php artisan test --filter=NotificationTest
php artisan test --filter=LivewirePredictionFormTest
```

## 📝 License

All rights reserved. Proprietary software.

