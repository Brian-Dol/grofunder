# Render.com Deployment Checklist

## Quick Start (5 minutes)

### Prerequisites
- [ ] GitHub account created
- [ ] Render.com account created at https://render.com

### Step 1: Git Setup (2 mins)
```bash
cd c:\Users\dolbr\Documents\growfunder\loan-management-system
git init
git add .
git commit -m "Initial: Growfunder with dashboard charts"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/growfunder.git
git push -u origin main
```

### Step 2: Create Database (2 mins)
1. Go to https://dashboard.render.com
2. Click "New" → "PostgreSQL"
3. Set name: `growfunder-db`
4. Click "Create Database"
5. **SAVE these details:**
   - Hostname: ________________
   - Port: 5432
   - Database: growfunder
   - Username: postgres
   - Password: ________________

### Step 3: Deploy Web Service (1 min setup + 5-10 min deploy)
1. Dashboard.render.com → "New" → "Web Service"
2. Connect GitHub repo: `growfunder`
3. Name: `growfunder`
4. Environment: PHP
5. Build: `./build.sh`
6. Start: `vendor/bin/heroku-php-apache2 public/`
7. **Add Environment Variables:**
   ```
   APP_NAME=Growfunder
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:TEMP_KEY_HERE
   APP_URL=https://growfunder.onrender.com
   
   DB_CONNECTION=pgsql
   DB_HOST=<FROM_STEP_2>
   DB_PORT=5432
   DB_DATABASE=growfunder
   DB_USERNAME=postgres
   DB_PASSWORD=<FROM_STEP_2>
   
   LOG_CHANNEL=stack
   LOG_LEVEL=info
   CACHE_DRIVER=file
   SESSION_DRIVER=database
   ```
8. Click "Create Web Service"

### Step 4: Generate App Key (1 min)
1. Wait for deployment to start
2. Go to your service → "Shell" tab
3. Run: `php artisan key:generate`
4. Copy output key (looks like: `base64:xxxxxxxxxxxxx`)
5. Go back to "Environment" 
6. Update `APP_KEY` with copied value
7. Save

### Step 5: Run Migrations (1 min)
1. In Shell, run: `php artisan migrate --force`
2. Create admin user:
   ```bash
   php artisan tinker
   ```
   ```php
   $user = new App\Models\User();
   $user->name = 'Admin';
   $user->email = 'admin@growfunder.local';
   $user->password = bcrypt('password');
   $user->save();
   exit
   ```

### Step 6: Done! 🎉
Visit: `https://growfunder.onrender.com`
- Email: `admin@growfunder.local`
- Password: `password`

---

## Deployment Time
- Setup: ~10 minutes
- First build: ~5-10 minutes
- **Total: ~15-20 minutes**

## After Deployment

✅ Share the public URL with users  
✅ Change default admin password  
✅ Update APP_URL if using custom domain  
✅ Monitor logs for issues  
✅ Consider upgrading for production use  

---

**Need help?** Check DEPLOYMENT_GUIDE.md for detailed instructions
