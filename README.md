# Formula 1 Predictions App

A comprehensive web application for Formula 1 fans to predict race outcomes and compete with others based on their predictions. Built with Laravel 11, this project demonstrates modern PHP development practices, database design, API integration, and full-stack web development skills.

## Table of Contents

- [Features](#features)
- [Pages & Routes](#pages--routes)
- [API Integration](#api-integration)
- [Scoring System](#scoring-system)
- [Rules](#rules)
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

## Development Roadmap

This project serves as a comprehensive learning journey through modern Laravel development. The roadmap is structured to build skills progressively, from fundamental concepts to advanced features.

### Development Approach
The project follows a structured learning path designed to:
- Build Laravel fundamentals through hands-on practice
- Implement core features using industry-standard patterns
- Integrate advanced Laravel ecosystem tools as skills develop
- Demonstrate full-stack development capabilities

### Technology Stack
- **Backend**: Laravel 11, PHP 8.4, SQLite (development)
- **Frontend**: Blade templates, Tailwind CSS, Vite
- **Future**: LiveWire, Volt, DaisyUI (planned)
- **Testing**: PEST framework
- **API**: F1 API integration

### 🎯 Phase 1: Laravel Fundamentals
- [ ] **Development Environment Setup**
  - [ ] Configure Laravel Herd development environment
  - [ ] Initialize Laravel 11 project with SQLite database
  - [ ] Set up environment configuration and Git repository
  - [ ] Establish development workflow and version control

- [ ] **Routing & View Architecture**
  - [ ] Implement RESTful routing for application pages
  - [ ] Design responsive layout components with Blade
  - [ ] Create reusable UI components following Laravel conventions
  - [ ] Implement dynamic navigation with active state management

- [ ] **Database Design & Eloquent ORM**
  - [ ] Design and implement database migrations for core entities
  - [ ] Create Eloquent models with proper relationships and constraints
  - [ ] Implement database interaction testing with Artisan Tinker
  - [ ] Apply mass assignment protection and model security

- [ ] **Advanced Model Relationships**
  - [ ] Implement complex database relationships (one-to-many, many-to-many)
  - [ ] Design pivot tables for complex data associations
  - [ ] Optimize database queries with eager loading
  - [ ] Resolve N+1 query problems through proper relationship design

- [ ] **Testing & Data Seeding**
  - [ ] Implement comprehensive test suite using PEST framework
  - [ ] Create database factories and seeders for development data
  - [ ] Import and validate historical F1 prediction data
  - [ ] Establish automated testing pipeline with SQLite in-memory database

- [ ] **Form Handling & Validation**
  - [ ] Build secure forms with CSRF protection and validation
  - [ ] Implement comprehensive server-side validation rules
  - [ ] Create reusable form components with error handling
  - [ ] Design user-friendly validation feedback systems

- [ ] **Controller Architecture**
  - [ ] Implement resource controllers following REST conventions
  - [ ] Utilize route model binding for clean, maintainable code
  - [ ] Organize application logic with proper separation of concerns
  - [ ] Implement middleware for route protection and filtering

### 🚀 Phase 2: Core Application Features
- [ ] **Authentication & Authorization System**
  - [ ] Implement custom authentication system with Laravel best practices
  - [ ] Design secure user registration and login workflows
  - [ ] Implement role-based access control with Gates and Policies
  - [ ] Create middleware for route protection and user authorization

- [ ] **Prediction Management System**
  - [ ] Design comprehensive prediction creation and editing interfaces
  - [ ] Implement preseason prediction system (championship orders, superlatives)
  - [ ] Create midseason prediction update functionality
  - [ ] Build prediction deadline enforcement and validation system

- [ ] **Advanced Prediction Interface (Future: LiveWire Integration)**
  - [ ] Implement real-time prediction forms with LiveWire components
  - [ ] Create interactive drag-and-drop driver ordering system
  - [ ] Design dynamic form sections with conditional logic
  - [ ] Build real-time validation and user feedback systems

- [ ] **User Dashboard & Analytics**
  - [ ] Create comprehensive user dashboard with statistics and rankings
  - [ ] Implement user profile management with prediction history
  - [ ] Design leaderboard system with real-time updates
  - [ ] Build admin interface for data management and analytics

- [ ] **Advanced Admin Features (Future: Volt Integration)**
  - [ ] Implement Volt-powered admin dashboard with real-time updates
  - [ ] Create advanced user management and analytics tools
  - [ ] Design comprehensive admin reporting and data visualization

- [ ] **Scoring & Results Processing**
  - [ ] Implement complex scoring algorithms for all prediction types
  - [ ] Handle edge cases including DNF, DNS, DNQ, DSQ scenarios
  - [ ] Support special race conditions (cancelled races, half-points)
  - [ ] Calculate statistical analysis including expected results and score ranges

### 🔧 Phase 3: Advanced Laravel Features
- [ ] **Performance Optimization**
  - [ ] Implement efficient pagination for large datasets
  - [ ] Optimize database queries with advanced eager loading
  - [ ] Integrate Laravel Debugbar for performance monitoring
  - [ ] Apply database query optimization and caching strategies

- [ ] **Email & Notification System**
  - [ ] Design mailable classes for user notifications and confirmations
  - [ ] Implement email preview and testing functionality
  - [ ] Configure multi-environment mail settings
  - [ ] Build queue-based background processing for email delivery

### 🌐 Phase 4: External Data Integration
- [ ] **F1 API Integration & Data Management**
  - [ ] Integrate F1 API SDK with comprehensive error handling
  - [ ] Implement robust race data fetching and processing
  - [ ] Design driver and team data retrieval systems
  - [ ] Create circuit information and historical data pages
  - [ ] Build real-time standings calculation and update system
  - [ ] Implement intelligent caching strategies for API responses

### 🤖 Phase 5: Automation & Bot Systems
- [ ] **Automated Prediction System Architecture**
  - [ ] Design scalable bot prediction system with queue-based processing
  - [ ] Implement secure bot user account management
  - [ ] Create scheduled task system for automated predictions
  - [ ] Build modular bot algorithm framework

- [ ] **Advanced Prediction Algorithms**
  - [ ] Implement historical data analysis bot (previous race results)
  - [ ] Create current standings-based prediction bot
  - [ ] Design previous year performance analysis bot
  - [ ] Build statistical analysis bots (random, weighted, average-based)
  - [ ] Implement machine learning-inspired prediction algorithms

### 📄 Phase 6: User Interface & Experience
- [ ] **Public Information Pages**
  - [ ] Design responsive home page with dynamic race information
  - [ ] Create comprehensive team and driver information pages
  - [ ] Build circuit and historical data presentation systems
  - [ ] Implement race schedule and results display interfaces
  - [ ] Design country-based F1 information and statistics pages
  - [ ] Create real-time standings and championship tracking pages

- [ ] **User Management Interfaces**
  - [ ] Build personalized user dashboard with comprehensive statistics
  - [ ] Design intuitive prediction creation and management interfaces
  - [ ] Create detailed user profile and prediction history pages
  - [ ] Implement comprehensive admin interface for data management

### 🎨 Phase 7: Frontend Development & Asset Management
- [ ] **Modern Asset Pipeline**
  - [ ] Configure Vite build system for optimal development workflow
  - [ ] Implement Tailwind CSS for responsive design system
  - [ ] Set up hot module replacement for efficient development
  - [ ] Optimize production asset bundling and delivery

- [ ] **Component-Based UI Architecture**
  - [ ] Design reusable Blade components following modern UI patterns
  - [ ] Implement responsive design system with mobile-first approach
  - [ ] Create accessible and user-friendly interface components
  - [ ] Build smooth animations and interactive user experiences

### 🎨 Phase 8: Advanced Frontend Technologies (Future)
- [ ] **Component Library Integration**
  - [ ] Integrate DaisyUI component library for enhanced UI consistency
  - [ ] Design custom F1-themed component system
  - [ ] Implement advanced animations and micro-interactions
  - [ ] Create comprehensive design system documentation

- [ ] **Real-Time User Experience**
  - [ ] Integrate Laravel LiveWire for dynamic, reactive interfaces
  - [ ] Implement real-time form validation and user feedback
  - [ ] Create interactive drag-and-drop prediction interfaces
  - [ ] Build seamless real-time data updates and notifications

- [ ] **Advanced Admin Interfaces**
  - [ ] Implement Laravel Volt for complex admin workflows
  - [ ] Design real-time admin dashboard with live data updates
  - [ ] Create advanced user management and analytics interfaces
  - [ ] Build comprehensive reporting and data visualization tools

### 🛠️ Phase 9: Technical Infrastructure & DevOps
- [ ] **Application Architecture & Configuration**
  - [ ] Establish Laravel project structure following industry best practices
  - [ ] Implement environment-specific configuration management
  - [ ] Design comprehensive error handling and logging systems
  - [ ] Configure development, staging, and production environments

- [ ] **Performance & Security Implementation**
  - [ ] Implement intelligent caching strategies for API and database operations
  - [ ] Design and implement security middleware including rate limiting
  - [ ] Optimize database performance through query optimization and indexing
  - [ ] Integrate monitoring, debugging, and performance analysis tools

### 📊 Phase 10: Data Visualization & Analytics (Future)
- [ ] **Interactive Data Visualization System**
  - [ ] Design and implement comprehensive charting system with LiveWire
  - [ ] Create interactive driver standings progression charts
  - [ ] Build team performance tracking and visualization tools
  - [ ] Implement prediction accuracy and trend analysis charts
  - [ ] Design real-time chart controls with dynamic data filtering
  - [ ] Optimize chart performance through intelligent data caching

### 🔄 Phase 11: Advanced Data Processing & Business Logic
- [ ] **Complex Scoring & Results Processing**
  - [ ] Implement statistical analysis including score range calculations
  - [ ] Design expected results and probability calculation systems
  - [ ] Handle complex race scenarios (DNF, DNS, DQ, etc.)
  - [ ] Build prediction deadline enforcement and validation systems
  - [ ] Create comprehensive race result processing and analysis tools

- [ ] **Edge Case & Special Scenario Handling**
  - [ ] Implement comprehensive handling of race anomalies and edge cases
  - [ ] Design systems for cancelled races and special scoring scenarios
  - [ ] Build driver and team change management throughout seasons

- [ ] **Superlatives & Special Predictions Management**
  - [ ] Design preseason prediction scoring algorithms
  - [ ] Implement comprehensive superlatives prediction system
  - [ ] Create admin interface for manual data entry and management
  - [ ] Build automated superlatives calculation from available API data
  - [ ] Design fallback systems for data not available through APIs

