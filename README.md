# Formula 1 Predictions App

A comprehensive web application for Formula 1 fans to predict race outcomes and compete with others based on their predictions. Built with Laravel 11, this project demonstrates modern PHP development practices, database design, API integration, and full-stack web development skills.

## Table of Contents

- [Features](#features)
- [Pages & Routes](#pages--routes)
- [API Integration](#api-integration)
- [Scoring System](#scoring-system)
- [Rules](#rules)
- [Migration from Previous Attempts](#migration-from-previous-attempts)
- [TODO](#todo)

## Features

- **Race Predictions**: Submit predictions for upcoming F1 races with drag-and-drop driver ordering
- **Preseason Predictions**: Predict team/driver championship order and superlatives before the season starts
- **Midseason Predictions**: Update championship predictions and make additional season-long predictions
- **Scoring System**: Earn points based on prediction accuracy across all prediction types
- **Leaderboards**: Compete with other users and automated prediction bots
- **Driver & Team Stats**: Comprehensive F1 statistics and information
- **User Profiles**: Track prediction history and performance
- **Data Visualization**: Interactive charts showing standings progression over time

## Pages & Routes

### Public Pages (Guest Access)
- `/` - Home page with hero section, last race details, and next race information
- `/login` - User authentication (Laravel built-in)
- `/register` - User registration (Laravel built-in)
- `/{year}/races` - Race schedule and results
- `/{year}/standings` - Championship standings
- `/{year}/standings/drivers` - Driver championship standings (ordered by championship position)
- `/{year}/standings/teams` - Team championship standings (ordered by championship position)
- `/{year}/standings/predictions` - General prediction standings
- `/{year}/standings/predictions/{username}` - User's predictions for the year
- `/{year}/race/{id}` - Specific race details and results
- `/countries` - All F1 countries
- `/country/{slug}` - Country information, drivers, teams, and circuits
- `/team/{slug}` - Team details, drivers, history, and current season stats
- `/driver/{slug}` - Driver details, career stats, and current season performance
- `/circuit/{slug}` - Circuit information and recent race results
- `/race/{slug}` - Race details, results, and weather information

### Protected Pages (Require Authentication)
- `/dashboard` - User dashboard with points, rankings, and prediction history
- `/settings/profile` - User profile settings
- `/settings/password` - Password change settings
- `/settings/appearance` - Appearance settings
- `/predict/{slug}` - Create or edit race predictions

## Real-Time Notification System

The application includes a comprehensive real-time notification system that provides instant updates to users about race results and prediction scoring.

### Features Implemented

- **Real-Time Notifications**: Instant browser notifications using Laravel Echo and WebSockets
- **Notification Dropdown**: LiveWire component in the header showing unread notification count
- **Notification Management**: Mark as read, mark all as read, and delete notifications
- **Email Notifications**: Queue-based email notifications for race results and prediction scoring
- **Database Storage**: All notifications are stored in the database for persistence
- **Toast Notifications**: Real-time toast notifications for immediate user feedback

### Components

- `NotificationDropdown` - LiveWire component for the header notification bell
- `NotificationReceived` - Event for broadcasting real-time notifications
- `NotificationService` - Service for sending notifications
- `/notifications` - Full notifications page with pagination

### Testing

```bash
# Run notification tests
php artisan test tests/Feature/NotificationTest.php
php artisan test tests/Feature/RealTimeNotificationTest.php

# Send test notifications
php artisan notifications:test --type=race
php artisan notifications:test --type=prediction --user=1
```

## API Integration

This application uses the [F1 API](https://f1api.dev/docs/sdk) for real-time Formula 1 data.

### Installation
```bash
npm install @f1api/sdk
```

### Usage
```javascript
import F1Api from "@f1api/sdk"

const f1Api = new F1Api()
const drivers = await f1Api.getDrivers()
```

## Scoring System

### Race Predictions
Users predict driver finishing positions for each race using drag-and-drop ordering. Points are awarded based on prediction accuracy:

| Position Difference | Points |
|-------------------|--------|
| Correct prediction | +25 |
| 1 position away | +18 |
| 2 positions away | +15 |
| 3 positions away | +12 |
| 4 positions away | +10 |
| 5 positions away | +8 |
| 6 positions away | +6 |
| 7 positions away | +4 |
| 8 positions away | +2 |
| 9 positions away | +1 |
| 10+ positions away | -1 to -25 |
| Correct DNF prediction | +10 |
| Incorrect DNF prediction | -10 |

### Preseason Predictions
**Team Championship Order**: Predict final team standings (1st-10th)
**Driver Championship Order**: Predict final driver standings (1st-20th)
**Superlatives**: Predict season-long achievements:
- Team with Most Podiums
- Driver with Most Podiums
- Team with Most DNFs
- Driver with Most DNFs
- Dirty Driver (most penalty points)
- Clean Driver (least penalty points)
- Most Fastest Laps
- Most Sprint Points
- Misses at least 1 Race
- Not driving in F1 next year

### Midseason Predictions
**Updated Championship Orders**: Revise team and driver championship predictions
**Season Predictions**: Additional season-long predictions:
- Team/Driver Fastest Pitstop
- Team/Driver with most P2 starts
- Total wins for specific drivers/teams
- Most Improved Team/Driver
- Weekend with Most Penalties
- Constructor/Driver Title Clinch locations
- Least racing laps in a weekend
- New team/driver podium winners

## Rules

- **Deadline**: Predictions must be submitted before the first qualifying session
- **Season Opener**: Due same time as the first race of the season
- **Summer Break**: Midseason predictions (if applicable) due before the first race of the second half

## Consolidated TODO List

### 🚨 Critical Priority (Fix Issues & Core Functionality)
- [x] **Fix Failing Tests**: Resolve all test failures and ensure 100% test pass rate ✅
- [x] **Real-Time Notifications**: Add notifications for race results and prediction scoring ✅
- [ ] **Data Visualization**: Add charts and graphs for standings progression
- [ ] **Mobile Optimization**: Ensure all interfaces work well on mobile devices
- [ ] **Performance Optimization**: Add caching and optimize database queries

### 🔧 High Priority (Core Features & User Experience)
- [ ] **Interactive Drag-and-Drop Prediction Interface**: Enhance LiveWire components for better UX
- [ ] **Advanced Admin Dashboard**: Create comprehensive admin interface with Volt
- [ ] **Leaderboard System**: Build real-time leaderboard with user rankings
- [x] **Email & Notification System**: Implement mailable classes and queue-based processing ✅
- [ ] **Prediction Deadline Enforcement**: Add validation and deadline management
- [ ] **Role-Based Access Control**: Implement Gates and Policies for user authorization

### 📊 Medium Priority (Advanced Features & Analytics)
- [ ] **Bot Prediction System**: Implement automated prediction bots with queue processing
- [ ] **Advanced Analytics**: Add statistical analysis and prediction accuracy tracking
- [ ] **Data Import/Export Tools**: Create tools to import historical F1 data and export predictions
- [ ] **Country-based F1 Information**: Design country-based F1 information and statistics pages
- [ ] **Real-time Standings**: Create real-time standings and championship tracking pages
- [ ] **Statistical Analysis**: Calculate expected results and probability calculations
- [ ] **Performance Monitoring**: Integrate monitoring, debugging, and performance analysis tools

### 🎨 Medium Priority (UI/UX Enhancements)
- [ ] **Component Library Integration**: Integrate DaisyUI for enhanced UI consistency
- [ ] **Advanced Animations**: Implement micro-interactions and smooth transitions
- [ ] **Design System Documentation**: Create comprehensive design system documentation
- [ ] **Theme System**: Implement light/dark theme switching
- [ ] **PWA Features**: Add offline support and app-like experience
- [ ] **Custom F1-Themed Component System**: Design F1-specific UI components

### 🔄 Medium Priority (Advanced Data Processing)
- [ ] **Complex Scoring Algorithms**: Implement statistical analysis and score range calculations
- [ ] **Edge Case Handling**: Handle DNF, DNS, DQ, cancelled races, and special scenarios
- [ ] **Superlatives Management**: Implement comprehensive superlatives prediction system
- [ ] **Driver/Team Change Management**: Build systems for mid-season changes
- [ ] **Prediction History Analytics**: Track and analyze user prediction patterns
- [ ] **Race Result Processing**: Create comprehensive race result processing and analysis tools

### 🤖 Medium Priority (Automation & Bot Systems)
- [ ] **Automated Prediction System Architecture**: Design scalable bot prediction system
- [ ] **Bot User Account Management**: Implement secure bot user account management
- [ ] **Scheduled Task System**: Create scheduled task system for automated predictions
- [ ] **Modular Bot Algorithm Framework**: Build framework for different bot algorithms
- [ ] **Historical Data Analysis Bot**: Implement bot based on previous race results
- [ ] **Current Standings-Based Bot**: Create bot using current championship standings
- [ ] **Previous Year Performance Bot**: Design bot analyzing previous year performance
- [ ] **Statistical Analysis Bots**: Build random, weighted, and average-based bots
- [ ] **Machine Learning-Inspired Algorithms**: Implement ML-inspired prediction algorithms

### 🛠️ Medium Priority (Technical Infrastructure)
- [ ] **Environment Configuration**: Configure development, staging, and production environments
- [ ] **API Development**: Create REST API for external integrations
- [ ] **Social Features**: Add user profiles, comments, and social interactions
- [ ] **Advanced Caching**: Implement intelligent caching strategies for all operations
- [ ] **Efficient Pagination**: Implement pagination for large datasets
- [ ] **Database Query Optimization**: Optimize queries with advanced eager loading
- [ ] **Laravel Debugbar Integration**: Add performance monitoring
- [ ] **Queue-Based Background Processing**: Implement for email delivery and heavy tasks

### 📈 Low Priority (Future Enhancements)
- [ ] **Machine Learning Integration**: Implement ML-inspired prediction algorithms
- [ ] **Advanced Charting**: Interactive driver standings progression charts
- [ ] **Team Performance Tracking**: Build team performance tracking and visualization tools
- [ ] **Prediction Accuracy Analysis**: Implement prediction accuracy and trend analysis charts
- [ ] **Real-time Chart Controls**: Design dynamic data filtering for charts
- [ ] **Chart Performance Optimization**: Optimize chart performance through intelligent data caching

### 🧹 Technical Debt (Code Quality & Maintenance)
- [ ] **Type Systems Implementation**: Add comprehensive type systems with PHPStan/Psalm
  - [ ] Add `declare(strict_types=1);` to all PHP files
  - [ ] Install and configure PHPStan for static analysis
  - [ ] Add comprehensive PHPDoc annotations to all models and classes
  - [ ] Add return type hints and parameter type hints to all methods
  - [ ] Configure type checking in CI/CD pipeline
- [ ] **Code Documentation**: Add comprehensive PHPDoc comments
- [ ] **Test Coverage**: Increase test coverage to 90%+
- [ ] **Error Handling**: Improve error handling and user feedback
- [ ] **Security Audit**: Conduct security review and implement improvements
- [ ] **Performance Monitoring**: Add monitoring and logging for production

### 📋 Migration Tasks (From Previous Attempts)
- [ ] **Enhanced Database Schema**: Migrate and enhance schema from F12024 project
- [ ] **Prediction Type System**: Implement prediction types as Laravel enums
- [ ] **JSON Storage Optimization**: Enhance JSON columns for complex prediction data
- [ ] **Historical Data Import**: Parse markdown prediction files for seed data
- [ ] **Component Architecture**: Migrate Angular component structure to Blade/LiveWire
- [ ] **Material Design Principles**: Implement using Tailwind CSS or DaisyUI
- [ ] **PWA Capabilities**: Add service worker capabilities for offline support
- [ ] **Theme System**: Implement light/dark theme switching from Next.js project
- [ ] **Analytics Dashboard**: Migrate charting system from Next.js using Laravel libraries
- [ ] **Edge Functions**: Implement Supabase-like functionality using Laravel queues
- [ ] **Prediction Format**: Implement structured JSON schema from markdown files
- [ ] **Admin Tools**: Create tools to import and manage prediction data
- [ ] **Player/User Distinction**: Implement yearly participation tracking
- [ ] **Points Tracking**: Add per-season points tracking system
- [ ] **Race Result Import**: Create race result import and processing system

### 🎯 Priority Order for Implementation:
1. **Critical Priority** - Fix any remaining issues and ensure core functionality works
2. **High Priority** - Complete core features that users need immediately
3. **Medium Priority** - Add advanced features that enhance user experience
4. **Low Priority** - Future enhancements and nice-to-have features
5. **Technical Debt** - Code quality improvements (can be done in parallel)
6. **Migration Tasks** - Integrate useful components from previous attempts

### 🚨 Critical Priority (Fix Issues & Core Functionality)
- [x] **Fix Failing Tests**: Resolve all test failures and ensure 100% test pass rate ✅
- [ ] **Real-Time Notifications**: Add notifications for race results and prediction scoring
- [ ] **Data Visualization**: Add charts and graphs for standings progression
- [ ] **Mobile Optimization**: Ensure all interfaces work well on mobile devices
- [ ] **Performance Optimization**: Add caching and optimize database queries

### 🔧 High Priority (Core Features & User Experience)
- [ ] **Interactive Drag-and-Drop Prediction Interface**: Enhance LiveWire components for better UX
- [ ] **Advanced Admin Dashboard**: Create comprehensive admin interface with Volt
- [ ] **Leaderboard System**: Build real-time leaderboard with user rankings
- [ ] **Email & Notification System**: Implement mailable classes and queue-based processing
- [ ] **Prediction Deadline Enforcement**: Add validation and deadline management

### 📊 Medium Priority (Advanced Features & Analytics)
- [ ] **Bot Prediction System**: Implement automated prediction bots with queue processing
- [ ] **Advanced Analytics**: Add statistical analysis and prediction accuracy tracking
- [ ] **Data Import/Export Tools**: Create tools to import historical F1 data and export predictions
- [ ] **Country-based F1 Information**: Design country-based F1 information and statistics pages
- [ ] **Real-time Standings**: Create real-time standings and championship tracking pages

### 🎨 Medium Priority (UI/UX Enhancements)
- [ ] **Component Library Integration**: Integrate DaisyUI for enhanced UI consistency
- [ ] **Advanced Animations**: Implement micro-interactions and smooth transitions
- [ ] **Design System Documentation**: Create comprehensive design system documentation
- [ ] **Theme System**: Implement light/dark theme switching
- [ ] **PWA Features**: Add offline support and app-like experience

### 🔄 Medium Priority (Advanced Data Processing)
- [ ] **Complex Scoring Algorithms**: Implement statistical analysis and score range calculations
- [ ] **Edge Case Handling**: Handle DNF, DNS, DQ, cancelled races, and special scenarios
- [ ] **Superlatives Management**: Implement comprehensive superlatives prediction system
- [ ] **Driver/Team Change Management**: Build systems for mid-season changes
- [ ] **Prediction History Analytics**: Track and analyze user prediction patterns

### 🛠️ Medium Priority (Technical Infrastructure)
- [ ] **Environment Configuration**: Configure development, staging, and production environments
- [ ] **Monitoring & Debugging**: Integrate monitoring, debugging, and performance analysis tools
- [ ] **API Development**: Create REST API for external integrations
- [ ] **Social Features**: Add user profiles, comments, and social interactions
- [ ] **Advanced Caching**: Implement intelligent caching strategies for all operations

### 📈 Low Priority (Future Enhancements)
- [ ] **Machine Learning Integration**: Implement ML-inspired prediction algorithms
- [ ] **Advanced Charting**: Interactive driver standings progression charts
- [ ] **Team Performance Tracking**: Build team performance tracking and visualization tools
- [ ] **Prediction Accuracy Analysis**: Implement prediction accuracy and trend analysis charts
- [ ] **Real-time Chart Controls**: Design dynamic data filtering for charts

### 🧹 Technical Debt (Code Quality & Maintenance)
- [ ] **Type Systems Implementation**: Add comprehensive type systems with PHPStan/Psalm
  - [ ] Add `declare(strict_types=1);` to all PHP files
  - [ ] Install and configure PHPStan for static analysis
  - [ ] Add comprehensive PHPDoc annotations to all models and classes
  - [ ] Add return type hints and parameter type hints to all methods
  - [ ] Configure type checking in CI/CD pipeline
- [ ] **Code Documentation**: Add comprehensive PHPDoc comments
- [ ] **Test Coverage**: Increase test coverage to 90%+
- [ ] **Error Handling**: Improve error handling and user feedback
- [ ] **Security Audit**: Conduct security review and implement improvements
- [ ] **Performance Monitoring**: Add monitoring and logging for production

### 📋 Migration Tasks (From Previous Attempts)
- [ ] **Enhanced Database Schema**: Migrate and enhance schema from F12024 project
- [ ] **Prediction Type System**: Implement prediction types as Laravel enums
- [ ] **JSON Storage Optimization**: Enhance JSON columns for complex prediction data
- [ ] **Historical Data Import**: Parse markdown prediction files for seed data
- [ ] **Component Architecture**: Migrate Angular component structure to Blade/LiveWire

### 🎯 Priority Order for Implementation:
1. **Critical Priority** - Fix any remaining issues and ensure core functionality works
2. **High Priority** - Complete core features that users need immediately
3. **Medium Priority** - Add advanced features that enhance user experience
4. **Low Priority** - Future enhancements and nice-to-have features
5. **Technical Debt** - Code quality improvements (can be done in parallel)
6. **Migration Tasks** - Integrate useful components from previous attempts

