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

## Migration from Previous Attempts

After reviewing the previous attempts in the `@previous/` folder, the following components and features should be migrated to this Laravel project:

### From BearsF1Prediction (PHP-based)
- **Database Schema Insights**: The `create_database.sql` shows a comprehensive schema with users, players, drivers, constructors, circuits, races, and predictions tables
- **Class Structure**: PHP classes for Circuits, Drivers, Constructors, Races, Users, and Players provide good reference for Laravel model relationships
- **Prediction Types**: Support for different prediction types (race, preseason, midseason) with JSON storage for flexible prediction data
- **Scoring System**: Basic scoring framework that can be enhanced in Laravel

**Migration Strategy**: 
- Convert the MySQL schema to Laravel migrations with proper Eloquent relationships
- Implement the prediction types as enums or constants in Laravel
- Use JSON columns for flexible prediction data storage
- Create comprehensive model relationships following Laravel conventions

### From f1-predictions-angular (Angular-based)
- **Component Architecture**: Well-structured component organization for drivers, races, users, circuits, and countries
- **Material Design UI**: Comprehensive Angular Material integration for modern UI components
- **Service Worker**: PWA capabilities with offline support

**Migration Strategy**:
- Convert Angular components to Blade components or LiveWire components
- Implement Material Design principles using Tailwind CSS or DaisyUI
- Consider PWA features using Laravel's service worker capabilities
- Use the component structure as reference for organizing Laravel views

### From f1-prediction (Next.js-based)
- **Modern Frontend Architecture**: Next.js App Router structure with hooks and data fetching
- **Supabase Integration**: Edge Functions for leaderboard calculations and data aggregation
- **Analytics Dashboard**: Charts and data visualization using Recharts
- **Theme System**: Light/dark theme implementation

**Migration Strategy**:
- Convert Next.js pages to Laravel routes and Blade views
- Implement Supabase-like functionality using Laravel's queue system and scheduled jobs
- Use Laravel's charting libraries (Chart.js, ApexCharts) for analytics
- Implement theme switching using Laravel's session management

### From Prediction Files (Markdown-based)
- **Scoring System**: Detailed prediction format showing driver order and fastest lap predictions
- **Season Structure**: Support for multiple races per season with consistent prediction format
- **User Predictions**: Historical prediction data that can be used for testing and validation

**Migration Strategy**:
- Parse the markdown prediction files to create seed data for testing
- Implement the prediction format as a structured JSON schema
- Use the historical data to validate scoring algorithms
- Create admin tools to import and manage prediction data

### From F12024 (Database-focused)
- **Enhanced Database Schema**: More detailed schema with proper relationships and constraints
- **Prediction Types**: Clear distinction between race, preseason, and midseason predictions
- **JSON Storage**: Flexible JSON storage for complex prediction data

**Migration Strategy**:
- Enhance current migrations based on the F12024 schema
- Implement the prediction type system as Laravel enums
- Use JSON columns for storing driver order predictions and additional data
- Create comprehensive database relationships

### Key Features to Migrate

1. **Prediction System**:
   - Race predictions (driver finishing order + fastest lap)
   - Preseason predictions (championship orders, superlatives)
   - Midseason predictions (updated championship orders)
   - JSON-based flexible prediction storage

2. **Scoring Algorithm**:
   - Position-based scoring (correct = 25pts, 1 position off = 18pts, etc.)
   - Fastest lap bonus points
   - DNF prediction handling
   - Season-long prediction scoring

3. **User Management**:
   - Player/User distinction (players represent yearly participation)
   - Points tracking per season
   - Prediction history and statistics

4. **Data Visualization**:
   - Leaderboards with real-time updates
   - Analytics dashboards with charts
   - Season progression tracking

5. **Admin Features**:
   - Race result import and processing
   - Prediction deadline management
   - User and data management tools

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
- [x] **Development Environment Setup**
  - [x] Configure Laravel Herd development environment
  - [x] Initialize Laravel 11 project with SQLite database
  - [x] Set up environment configuration and Git repository
  - [x] Establish development workflow and version control

- [x] **Routing & View Architecture**
  - [x] Implement RESTful routing for application pages
  - [x] Design responsive layout components with Blade
  - [x] Create reusable UI components following Laravel conventions
  - [x] Implement dynamic navigation with active state management

- [x] **Database Design & Eloquent ORM**
  - [x] Design and implement database migrations for core entities
  - [x] Create Eloquent models with proper relationships and constraints
  - [x] Implement database interaction testing with Artisan Tinker
  - [x] Apply mass assignment protection and model security

- [x] **Advanced Model Relationships**
  - [x] Implement complex database relationships (one-to-many, many-to-many)
  - [x] Design pivot tables for complex data associations
  - [x] Optimize database queries with eager loading
  - [x] Resolve N+1 query problems through proper relationship design

- [x] **Testing & Data Seeding**
  - [x] Implement comprehensive test suite using PEST framework
  - [x] Create database factories and seeders for development data
  - [x] Import and validate historical F1 prediction data
  - [x] Establish automated testing pipeline with SQLite in-memory database

- [x] **Form Handling & Validation**
  - [x] Build secure forms with CSRF protection and validation
  - [x] Implement comprehensive server-side validation rules
  - [x] Create reusable form components with error handling
  - [x] Design user-friendly validation feedback systems

- [x] **Controller Architecture**
  - [x] Implement resource controllers following REST conventions
  - [x] Utilize route model binding for clean, maintainable code
  - [x] Organize application logic with proper separation of concerns
  - [x] Implement middleware for route protection and filtering

### 🚀 Phase 2: Core Application Features
- [x] **Authentication & Authorization System**
  - [x] Implement custom authentication system with Laravel best practices
  - [x] Design secure user registration and login workflows
  - [ ] Implement role-based access control with Gates and Policies
  - [x] Create middleware for route protection and user authorization

- [x] **Prediction Management System**
  - [x] Design comprehensive prediction creation and editing interfaces
  - [x] Implement preseason prediction system (championship orders, superlatives)
  - [x] Create midseason prediction update functionality
  - [x] Build prediction deadline enforcement and validation system

- [ ] **Advanced Prediction Interface (Future: LiveWire Integration)**
  - [ ] Implement real-time prediction forms with LiveWire components
  - [ ] Create interactive drag-and-drop driver ordering system
  - [ ] Design dynamic form sections with conditional logic
  - [ ] Build real-time validation and user feedback systems

- [x] **User Dashboard & Analytics**
  - [x] Create comprehensive user dashboard with statistics and rankings
  - [x] Implement user profile management with prediction history
  - [ ] Design leaderboard system with real-time updates
  - [ ] Build admin interface for data management and analytics

- [ ] **Advanced Admin Features (Future: Volt Integration)**
  - [ ] Implement Volt-powered admin dashboard with real-time updates
  - [ ] Create advanced user management and analytics tools
  - [ ] Design comprehensive admin reporting and data visualization

- [x] **Scoring & Results Processing**
  - [x] Implement complex scoring algorithms for all prediction types
  - [x] Handle edge cases including DNF, DNS, DNQ, DSQ scenarios
  - [x] Support special race conditions (cancelled races, half-points)
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
- [x] **F1 API Integration & Data Management**
  - [x] Integrate F1 API SDK with comprehensive error handling
  - [x] Implement robust race data fetching and processing
  - [x] Design driver and team data retrieval systems
  - [x] Create circuit information and historical data pages
  - [x] Build real-time standings calculation and update system
  - [x] Implement intelligent caching strategies for API responses

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
- [x] **Public Information Pages**
  - [x] Design responsive home page with dynamic race information
  - [x] Create comprehensive team and driver information pages
  - [x] Build circuit and historical data presentation systems
  - [x] Implement race schedule and results display interfaces
  - [ ] Design country-based F1 information and statistics pages
  - [ ] Create real-time standings and championship tracking pages

- [x] **User Management Interfaces**
  - [x] Build personalized user dashboard with comprehensive statistics
  - [x] Design intuitive prediction creation and management interfaces
  - [x] Create detailed user profile and prediction history pages
  - [ ] Implement comprehensive admin interface for data management

### 🎨 Phase 7: Frontend Development & Asset Management
- [x] **Modern Asset Pipeline**
  - [x] Configure Vite build system for optimal development workflow
  - [x] Implement Tailwind CSS for responsive design system
  - [x] Set up hot module replacement for efficient development
  - [x] Optimize production asset bundling and delivery

- [x] **Component-Based UI Architecture**
  - [x] Design reusable Blade components following modern UI patterns
  - [x] Implement responsive design system with mobile-first approach
  - [x] Create accessible and user-friendly interface components
  - [x] Build smooth animations and interactive user experiences

### 🎨 Phase 8: Advanced Frontend Technologies (Future)
- [ ] **Component Library Integration**
  - [ ] Integrate DaisyUI component library for enhanced UI consistency
  - [ ] Design custom F1-themed component system
  - [ ] Implement advanced animations and micro-interactions
  - [ ] Create comprehensive design system documentation

- [x] **Real-Time User Experience**
  - [x] Integrate Laravel LiveWire for dynamic, reactive interfaces
  - [x] Implement real-time form validation and user feedback
  - [ ] Create interactive drag-and-drop prediction interfaces
  - [x] Build seamless real-time data updates and notifications

- [ ] **Advanced Admin Interfaces**
  - [ ] Implement Laravel Volt for complex admin workflows
  - [ ] Design real-time admin dashboard with live data updates
  - [ ] Create advanced user management and analytics interfaces
  - [ ] Build comprehensive reporting and data visualization tools

### 🛠️ Phase 9: Technical Infrastructure & DevOps
- [x] **Application Architecture & Configuration**
  - [x] Establish Laravel project structure following industry best practices
  - [x] Implement environment-specific configuration management
  - [x] Design comprehensive error handling and logging systems
  - [ ] Configure development, staging, and production environments

- [x] **Performance & Security Implementation**
  - [x] Implement intelligent caching strategies for API and database operations
  - [x] Design and implement security middleware including rate limiting
  - [x] Optimize database performance through query optimization and indexing
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

## TODO

### Immediate Next Steps (High Priority)
- [x] **Complete Model Relationships**: Add missing foreign key relationships between models
- [x] **Implement Role-Based Access Control**: Add Gates and Policies for user authorization
- [x] **Create Prediction Forms**: Build forms for creating and editing predictions
- [x] **Add Data Import/Export**: Create tools to import historical F1 data and export predictions
- [x] **Implement Leaderboard System**: Build real-time leaderboard with user rankings
- [x] **Add Admin Interface**: Create comprehensive admin dashboard for data management

### Medium Priority
- [ ] **Interactive Prediction Interface**: Implement drag-and-drop driver ordering with LiveWire
- [ ] **Real-Time Notifications**: Add notifications for race results and prediction scoring
- [ ] **Data Visualization**: Add charts and graphs for standings progression
- [ ] **Mobile Optimization**: Ensure all interfaces work well on mobile devices
- [ ] **Performance Optimization**: Add caching and optimize database queries

### Future Enhancements
- [ ] **Bot Prediction System**: Implement automated prediction bots
- [ ] **Advanced Analytics**: Add statistical analysis and prediction accuracy tracking
- [ ] **Social Features**: Add user profiles, comments, and social interactions
- [ ] **API Development**: Create REST API for external integrations
- [ ] **PWA Features**: Add offline support and app-like experience

### Technical Debt
- [ ] **Type Systems Implementation**: Implement comprehensive type systems with PHPStan/Psalm
  - [ ] Add `declare(strict_types=1);` to all PHP files
  - [ ] Install and configure PHPStan for static analysis
  - [ ] Add comprehensive PHPDoc annotations to all models and classes
  - [ ] Add return type hints and parameter type hints to all methods
  - [ ] Configure type checking in CI/CD pipeline
  - [ ] See `.cursor/rules/type-systems.mdc` for detailed implementation guide
- [ ] **Code Documentation**: Add comprehensive PHPDoc comments
- [ ] **Test Coverage**: Increase test coverage to 90%+
- [ ] **Error Handling**: Improve error handling and user feedback
- [ ] **Security Audit**: Conduct security review and implement improvements
- [ ] **Performance Monitoring**: Add monitoring and logging for production

