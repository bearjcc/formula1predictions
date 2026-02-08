# Deployment Plan - F1 Prediction Game

## Overview
This deployment plan covers moving the F1 Prediction Game from development (Tailscale/Localhost) to production on a VPS (Hetzner/DigitalOcean).

## Go-Live Audit (2026-02-08)

**Verdict: Ready to publish after completing the pre-launch checklist below.**

**Fixes applied during audit:**
- Admin routes now use `admin` middleware so only admin/mod role can access `/admin/*` (was previously auth-only; some actions used Gates but GET dashboard/users/etc. did not).
- Stripe webhook route moved outside the `auth` middleware group so Stripe can POST successfully; CSRF exclusion added for `stripe/webhook` in `bootstrap/app.php`.

**Pre-launch (must-do):**
1. Set production `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.com`.
2. Create at least one admin user with email listed in `User::hasRole('admin')` (see `app/Models/User.php`; default list is `admin@example.com`, `system@example.com`) or run `php artisan db:seed --class=TestUserSeeder` and change that user’s email to your admin email, then update the hardcoded list in `User.php` to match.
3. Configure Stripe live keys and webhook endpoint URL in Stripe Dashboard; set `STRIPE_WEBHOOK_SECRET` in `.env`.
4. Run `php artisan test` (or `.\scripts\test-batches.ps1` on Windows if full run times out); run `vendor/bin/pint --dirty`; run `composer audit`; run `npm run build`.
5. Optional: Restrict or remove public `api/f1/*` routes in production if you do not want external callers (e.g. `/api/f1/test`, `/api/f1/cache/{year}`).

**Already in good shape:** Config uses `config()` (no `env()` in app code); `.env` in `.gitignore`; policies and Gates for predictions/races/admin; `composer audit` clean; scoring and auth tests passing.

---

## Phase 1: Pre-Deployment Checklist ✅

### Application Status
- [x] Phase 2: Core prediction logic complete
- [x] Phase 3: Leaderboard and user stats complete
- [x] Season Supporter badge system with Stripe integration
- [ ] Mini-Leagues / Private Groups (WIP)
- [ ] Real-time notifications (WIP)

### Code Quality
- [ ] All tests passing (`php artisan test`)
- [ ] Code formatted with Pint (`./vendor/bin/pint`)
- [ ] Security audit passed (`composer audit`)
- [ ] Frontend assets built (`npm run build`)

### Configuration
- [x] Laravel Cashier installed and configured
- [ ] Stripe keys configured (test mode → production)
- [ ] Database backup strategy in place
- [ ] Environment variables documented

## Phase 2: Infrastructure Setup

### Option A: Hetzner Cloud (Recommended)

**Pros:**
- Cost-effective (€4-6/month for CX22)
- Excellent performance (NVMe SSD)
- Data centers in Europe (good for F1 audience)
- Simple hourly billing

**Server Specs (Recommended):**
- Plan: CX22 or CPX22
- CPU: 2 vCPU
- RAM: 4GB
- Storage: 40GB NVMe SSD
- Location: Finland (fsn1) or Germany (nbg1)
- Cost: ~€6/month

**Setup Steps:**

1. **Create Server**
   ```bash
   # Via Hetzner Cloud Console
   - Choose Ubuntu 22.04 LTS
   - Add SSH key
   - Enable backups (€0.70/month)
   ```

2. **Initial Server Hardening**
   ```bash
   # Update system
   apt update && apt upgrade -y

   # Install essential tools
   apt install -y curl wget git vim ufw fail2ban

   # Configure firewall
   ufw allow 22/tcp
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw enable

   # Configure fail2ban
   systemctl enable fail2ban
   systemctl start fail2ban
   ```

3. **Install PHP and Extensions**
   ```bash
   # Add PHP repository
   apt install -y software-properties-common
   add-apt-repository ppa:ondrej/php

   # Install PHP 8.2+ and extensions
   apt install -y php8.2 php8.2-fpm php8.2-mysql \
     php8.2-mbstring php8.2-xml php8.2-curl \
     php8.2-zip php8.2-bcmath php8.2-intl \
     php8.2-gd php8.2-sqlite3
   ```

4. **Install Nginx**
   ```bash
   apt install -y nginx
   systemctl enable nginx
   systemctl start nginx
   ```

5. **Install PostgreSQL or MySQL**
   ```bash
   # For PostgreSQL
   apt install -y postgresql postgresql-contrib

   # For MySQL
   apt install -y mysql-server
   mysql_secure_installation
   ```

6. **Install Composer**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   mv composer.phar /usr/local/bin/composer
   chmod +x /usr/local/bin/composer
   ```

7. **Install Node.js**
   ```bash
   curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
   apt install -y nodejs
   ```

### Option B: DigitalOcean

**Pros:**
- Excellent documentation
- Managed databases available
- Community tutorials

**Server Specs (Recommended):**
- Plan: Basic droplet
- CPU: 1-2 vCPU
- RAM: 2-4GB
- Storage: 60GB SSD
- Cost: ~$24-48/month

**Setup:** Similar to Hetzner, use DigitalOcean's "Laravel on Ubuntu" guide.

### Option C: Laravel Forge (Easiest)

**Pros:**
- Automated deployment
- SSL certificates included
- Server management handled
- Quick rollbacks

**Cons:**
- $12/month Forge fee (in addition to server)
- Less control over server

**Setup Steps:**
1. Create account on forge.laravel.com
2. Connect Hetzner/DigitalOcean account
3. Provision server through Forge
4. Connect GitHub repository
5. Configure deployment script

## Phase 3: Application Deployment

### 3.1. Clone Repository

```bash
cd /var/www
git clone https://github.com/bearjcc/formula1predictions.git f1-game
cd f1-game
```

### 3.2. Environment Configuration

```bash
cp .env.production .env
# Edit .env with production values
```

**Critical Environment Variables:**
```env
APP_NAME="F1 Predictions"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=f1_predictions
DB_USERNAME=f1_user
DB_PASSWORD=secure_password

# Stripe
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
CASHIER_CURRENCY=usd
CASHIER_CASHIER_KEY=xxx
CASHIER_CASHIER_SECRET=xxx

# Queue
QUEUE_CONNECTION=redis

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # or production SMTP
MAIL_PORT=2525
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx

# Security
APP_KEY=generated_via_artisan_key_generate
```

### 3.3. Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install frontend dependencies
npm install
npm run build
```

### 3.4. Database Setup

```bash
# Create database (PostgreSQL)
sudo -u postgres createdb f1_predictions

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force
```

### 3.5. Application Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize composer
composer dump-autoload --optimize
```

### 3.6. File Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/f1-game

# Set permissions
chmod -R 755 /var/www/f1-game
chmod -R 775 /var/www/f1-game/storage
chmod -R 775 /var/www/f1-game/bootstrap/cache
```

### 3.7. Nginx Configuration

```nginx
# /etc/nginx/sites-available/f1-game
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/f1-game/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
ln -s /etc/nginx/sites-available/f1-game /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### 3.8. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
apt install -y certbot python3-certbot-nginx

# Obtain certificate
certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
certbot renew --dry-run
```

## Phase 4: Stripe Configuration

### 4.1. Create Stripe Account

1. Sign up at stripe.com
2. Verify business details
3. Add bank account for payouts

### 4.2. Configure Products

**Via Stripe Dashboard:**
1. Go to Products → Add Product
2. Name: "Season Supporter"
3. Description: "F1 Predictions - Season Supporter Badge"
4. Price: $10.00 (one-time payment)
5. Copy Price ID to config/services.php

**Config Update:**
```php
// config/services.php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'season_supporter_price_id' => env('STRIPE_PRICE_ID', 'price_xxx'),
    'season_supporter_amount' => 1000, // $10.00 in cents
],
```

### 4.3. Configure Webhooks

**Via Stripe Dashboard:**
1. Go to Developers → Webhooks → Add Endpoint
2. URL: https://your-domain.com/stripe/webhook
3. Events to listen for:
   - checkout.session.completed
   - payment_intent.succeeded
   - payment_intent.payment_failed

4. Add signing secret to .env:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_xxx
   ```

## Phase 5: Monitoring and Maintenance

### 5.1. Monitoring Tools

**Application Monitoring:**
- Install Laravel Telescope (development)
- Configure error tracking (Sentry/Bugsnag)
- Set up uptime monitoring (UptimeRobot/Pingdom)

**Server Monitoring:**
- Install htop, iotop, netdata
- Configure logrotate
- Set up disk space alerts

### 5.2. Backup Strategy

**Database Backups:**
```bash
# Daily backups to offsite storage
#!/bin/bash
DATE=$(date +%Y%m%d)
pg_dump f1_predictions | gzip > /backup/f1_$DATE.sql.gz
# Upload to S3/Backblaze B2
```

**Application Backups:**
- Git-based (code history)
- S3 sync for user uploads (if any)

### 5.3. Update Strategy

**Zero-Downtime Deployments:**
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
systemctl reload nginx
systemctl restart php8.2-fpm
```

## Phase 6: Security Hardening

### 6.1. Application Security

```bash
# Generate application key
php artisan key:generate

# Set permissions
chmod 600 /var/www/f1-game/.env
chmod 644 /var/www/f1-game/storage/oauth-*.key

# Configure trusted proxies
# config/trustedproxy.php
```

### 6.2. Server Security

- [ ] SSH key authentication only (no password)
- [ ] Change SSH port from 22
- [ ] Install and configure fail2ban
- [ ] Regular security updates
- [ ] Monitor logs for suspicious activity

### 6.3. Application Security

- [ ] Force HTTPS
- [ ] CSRF protection enabled
- [ ] SQL injection prevention (Eloquent)
- [ ] XSS prevention (Blade)
- [ ] Rate limiting on sensitive routes
- [ ] Input validation

## Phase 7: Performance Optimization

### 7.1. Caching

- Redis for cache, queue, and sessions
- Configure HTTP caching headers
- CDN for static assets (optional)

### 7.2. Queue Worker

```bash
# Install Supervisor
apt install -y supervisor

# Configure queue worker
# /etc/supervisor/conf.d/f1-worker.conf
[program:f1-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/f1-game/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/f1-game/storage/logs/worker.log

# Start supervisor
supervisorctl reread
supervisorctl update
supervisorctl start f1-worker:*
```

### 7.3. Database Optimization

- Add indexes for common queries
- Configure connection pooling
- Regular VACUUM/ANALYZE (PostgreSQL)

## Phase 8: Post-Deployment Checklist

### Testing
- [ ] All pages load correctly
- [ ] Authentication works
- [ ] Predictions can be created
- [ ] Scoring system works
- [ ] Stripe checkout completes successfully
- [ ] Webhooks receive events correctly
- [ ] Email notifications work
- [ ] Error handling tested

### Documentation
- [ ] Update README with production URL
- [ ] Document admin credentials
- [ ] Create runbook for common issues
- [ ] Document rollback procedure

### Launch
- [ ] DNS configured correctly
- [ ] SSL certificate valid
- [ ] Performance benchmarks acceptable
- [ ] Monitoring alerts configured
- [ ] Team notified of launch

## Estimated Timeline

- Phase 1-2: Pre-dep (1-2 days)
- Phase 2: Infrastructure (1 day)
- Phase 3: Deployment (1 day)
- Phase 4: Stripe config (2 hours)
- Phase 5-6: Monitoring & Security (1 day)
- Phase 7: Performance (1 day)
- Phase 8: Testing & Launch (1 day)

**Total:** 5-7 days to production launch

## Estimated Monthly Costs

**Hetzner Option:**
- Server (CX22): €6
- Backups: €0.70
- Domain: ~€10/year (~€0.83/month)
- **Total:** ~€7.50/month

**DigitalOcean Option:**
- Droplet (2GB/1CPU): $24
- Database (managed): $15
- **Total:** ~$39/month

**Laravel Forge Option:**
- Forge subscription: $12
- Server (via DO): $24
- **Total:** ~$36/month

## Next Steps

1. Choose hosting provider (Hetzner recommended)
2. Set up test environment first
3. Configure Stripe in test mode
4. Run full integration tests
5. Deploy to production
6. Monitor for 48 hours
7. Announce launch to community

---

**Prepared:** 2026-02-06
**Last Updated:** 2026-02-06
**Version:** 1.0
