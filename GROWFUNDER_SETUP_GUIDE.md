# Growfunder MVP Setup Guide

## Quick Start (Windows)

### Prerequisites
- **PHP 8.2+** (Download from: https://windows.php.net/downloads/)
- **Composer** (https://getcomposer.org/download/)
- **Node.js 18+** (https://nodejs.org/)
- **SQLite** or **PostgreSQL** (SQLite included with PHP)
- **Git** (optional, for version control)

### Step 1: Verify Prerequisites
```powershell
php --version          # Should show PHP 8.2+
composer --version     # Should show Composer 2.x
node --version         # Should show Node 18+
```

### Step 2: Install Dependencies

```powershell
cd c:\Users\dolbr\Documents\growfunder\loan-management-system

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Build frontend assets
npm run build
```

### Step 3: Environment Setup

```powershell
# Copy environment template
copy .env.example .env

# Generate app key
php artisan key:generate
```

**Edit `.env` file and set:**
```
APP_NAME=Growfunder
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
# SQLite: just needs the file path
DB_DATABASE=database/growfunder.sqlite

MAIL_MAILER=log
```

### Step 4: Database Setup

```powershell
# Create SQLite database file
touch database/growfunder.sqlite

# Run migrations
php artisan migrate

# Seed default data (Chart of Accounts)
php artisan db:seed --class=ChartOfAccountsSeeder

# Install FilamentShield (roles/permissions)
php artisan shield:install

# Choose "yes" for all seeding prompts
```

### Step 5: Create Super Admin User

```powershell
php artisan shield:super-admin
```
**Follow prompts to create admin user**

### Step 6: Run Development Server

```powershell
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Watch for frontend changes
npm run dev
```

You'll see:
```
Laravel development server started: http://127.0.0.1:8000
```

Access the admin panel at: **http://localhost:8000/admin**

---

## Growfunder Customizations Needed

### 1. Data Model Updates (Priority 1)

**Current Issue:** System uses generic "Borrower" model
**Need:** Adapt for:
- Farmers (with mobile number as primary ID)
- Cooperatives (agent grouping)
- Growfunder Admin (multi-scope dashboard)

**Files to modify:**
- `app/Models/Borrower.php` → Rename to `Farmer` or extend
- `app/Models/User.php` → Add role field (farmer/agent/admin/investor)
- Create `app/Models/Cooperative.php`

### 2. Dashboard Customization (Priority 2)

**Current:** Single admin dashboard
**Need:** Three dashboards
- **Agent Portal:** Add loans for farmers, track repayments, view their farmers
- **Admin Dashboard:** View all agents, see Growfunder data aggregation
- **Investor Dashboard:** View active loans, ROI, farmer performance

**Files to modify:**
- `app/Filament/Resources/` → Create role-specific panels
- `routes/web.php` → Add routing for investor dashboard

### 3. Mobile Number as ID (Priority 3)

**Current:** Uses user IDs
**Need:** Make mobile number the primary identifier for farmers

**Changes:**
- Add migration: `database/migrations/2024_xx_xx_add_mobile_to_farmers.php`
- Update Farmer model to use mobile as UUID
- Add OTP verification flow (optional for MVP)

### 4. Payment Routing (Priority 4)

**Current:** Direct wallet transfers
**Need:** Track payments FROM farmers TO Growfunder platform

**Add:**
- Mobile money integration layer (M-Pesa, etc. - depends on region)
- Payment gateway API wrapper
- Webhook handlers for payment confirmation

### 5. Data Aggregation (Priority 5 - Your Differentiator)

**MVP doesn't include this yet. Build:**
- API layer to aggregate data from legacy cooperatives
- Data normalization (convert different date formats, field names)
- Sync scheduler (cron job to pull data daily)

---

## Project Structure

```
loan-management-system/
├── app/
│   ├── Filament/          # Admin panel resources
│   ├── Models/            # Database models (Borrower, Lender, etc.)
│   ├── Services/          # Business logic
│   └── Http/Controllers/  # API controllers
├── database/
│   ├── migrations/        # Database schema changes
│   └── seeders/           # Sample data
├── resources/
│   ├── views/             # Blade templates
│   └── css/js/            # Frontend assets
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
├── config/                # Configuration files
└── public/                # Public assets (CSS, JS, images)
```

---

## Testing the Setup

### 1. Access Admin Panel
- URL: http://localhost:8000/admin
- Login with super-admin credentials created in Step 5

### 2. Explore Features
- Create a Lender account
- Create a Borrower (Farmer)
- Create a Loan Application
- Track repayments

### 3. Check Database
```powershell
# View SQLite database
php artisan tinker
>>> DB::table('users')->get();
```

---

## Troubleshooting

### "PHP command not found"
- Add PHP to Windows PATH or use full path: `C:\path\to\php.exe artisan`

### "Composer not found"
- Reinstall Composer or add to PATH

### "SQLSTATE[HY000]: General error: 1 out of memory"
- Check database permissions on `database/` folder
- Try PostgreSQL instead

### "Asset compilation failed"
```powershell
npm run build
php artisan optimize:clear
```

### Cannot access http://localhost:8000
- Ensure `php artisan serve` is running in Terminal 1
- Check firewall settings
- Try: http://127.0.0.1:8000

---

## Next Steps (After MVP)

1. **Deploy to Cloud** (AWS EC2, Digital Ocean, Railway)
   - Use PostgreSQL instead of SQLite
   - Configure proper auth (JWT for API)
   - Setup SSL/HTTPS

2. **Add Investor Dashboard** (Phase 2)
   - New Filament resource for investor viewing
   - Real-time ROI calculations
   - Farmer performance analytics

3. **Build Data Integration** (Phase 2)
   - Connect to cooperative legacy systems
   - Automate data sync
   - Data quality checks

4. **Mobile App** (Phase 3)
   - Flutter or React Native for farmer app
   - OTP login
   - Payment submission
   - Repayment tracking

---

## Key Features Already Available

✅ **Agent Portal** - Add loans, track borrowers  
✅ **Admin Dashboard** - View all activities, financial reports  
✅ **Loan Workflow** - Application → Approval → Disbursement → Repayment  
✅ **Accounting** - Double-entry, Chart of Accounts, Journal entries  
✅ **Role-Based Access** - FilamentShield for granular permissions  
✅ **PDF Reports** - Export loans, repayments, financial statements  
✅ **Multi-Wallet** - Loan wallet, expense wallet, savings wallet  
✅ **Email Notifications** - Loan approved, repayment reminders  

---

## Support Links

- Laravel Docs: https://laravel.com/docs
- Filament Docs: https://filamentphp.com
- FilamentShield: https://filamentphp.com/community/filament-shield
- Laravel Wallet: https://bavix.github.io/laravel-wallet/
