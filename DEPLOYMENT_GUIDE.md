# Growfunder Deployment Guide - Render.com

## Overview
This guide walks you through deploying the Growfunder application to Render.com's free tier.

**What you'll get:**
- Free PostgreSQL database (5 GB storage)
- Free web service (0.5 GB RAM, shared CPU)
- Free HTTPS with automatic SSL
- Custom subdomain (e.g., `growfunder.onrender.com`)
- Automatic deployments from GitHub

---

## Prerequisites

1. **GitHub Account** - Required for Render.com integration
2. **Code committed to GitHub** - Push your code to a GitHub repository
3. **Render.com Account** - Sign up at https://render.com (free)

---

## Step 1: Push Code to GitHub

1. **Create a GitHub repository:**
   - Go to https://github.com/new
   - Name: `growfunder` (or your preference)
   - Make it **Public** (easier for CI/CD)
   - Click "Create repository"

2. **Commit and push your code:**
   ```bash
   cd c:\Users\dolbr\Documents\growfunder\loan-management-system
   git init
   git add .
   git commit -m "Initial commit: Dashboard charts and optimizations"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/growfunder.git
   git push -u origin main
   ```

---

## Step 2: Create PostgreSQL Database on Render.com

1. **Sign in to Render.com** - https://dashboard.render.com

2. **Create PostgreSQL Database:**
   - Click "New" → "PostgreSQL"
   - **Name:** `growfunder-db`
   - **Database:** `growfunder`
   - **User:** `postgres`
   - **Region:** Choose closest to you
   - **Version:** Latest available
   - Click "Create Database"

3. **Note down connection details:**
   - **Hostname** (e.g., `dpg-xyz.render.com`)
   - **Port** (5432)
   - **Database** (growfunder)
   - **Username** (postgres)
   - **Password** (auto-generated, shown once)

---

## Step 3: Deploy Web Service

1. **From Render Dashboard:**
   - Click "New" → "Web Service"
   - Choose "Connect my own GitHub repo"
   - Search and select: `yourUsername/growfunder`
   - Click "Connect"

2. **Configure Web Service:**
   - **Name:** `growfunder`
   - **Environment:** PHP
   - **Build Command:** `./build.sh`
   - **Start Command:** `vendor/bin/heroku-php-apache2 public/`
   - **Region:** Same as database
   - **Branch:** `main`

3. **Add Environment Variables:**
   Click "Environment" and add:
   
   ```
   APP_NAME=Growfunder
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:YOUR_KEY_HERE
   APP_URL=https://growfunder.onrender.com
   
   DB_CONNECTION=pgsql
   DB_HOST=<your-db-host>.render.com
   DB_PORT=5432
   DB_DATABASE=growfunder
   DB_USERNAME=postgres
   DB_PASSWORD=<your-db-password>
   
   LOG_CHANNEL=stack
   LOG_LEVEL=info
   
   CACHE_DRIVER=file
   SESSION_DRIVER=database
   QUEUE_CONNECTION=sync
   ```

   **Note:** Replace `<your-db-host>` and `<your-db-password>` with actual values from Step 2

4. **Create Render Service:**
   - Click "Create Web Service"
   - Wait for deployment (~5-10 minutes)

---

## Step 4: Generate APP_KEY

Once deployment starts:

1. Open Render dashboard
2. Go to your web service → "Shell"
3. Run: `php artisan key:generate`
4. Copy the generated key
5. Go to "Environment" and update `APP_KEY`

---

## Step 5: Run Database Migrations

After APP_KEY is set:

1. In the Shell, run:
   ```bash
   php artisan migrate --force
   ```

2. Create admin user:
   ```bash
   php artisan tinker
   > $user = new App\Models\User();
   > $user->name = 'Admin';
   > $user->email = 'admin@growfunder.local';
   > $user->password = bcrypt('password');
   > $user->organization_id = 1;
   > $user->branch_id = 1;
   > $user->save();
   > exit
   ```

---

## Step 6: Access Your App

Your app is now live at: `https://growfunder.onrender.com`

**Login credentials:**
- Email: `admin@growfunder.local`
- Password: `password`

---

## Troubleshooting

### App not loading
- Check logs in Render dashboard
- Verify all environment variables are set
- Ensure database connection is working

### Database connection errors
- Verify DB credentials in environment variables
- Check database is running (Render dashboard)
- Ensure IP whitelist allows Render service

### Migrations failed
- Check Shell logs for errors
- Verify database structure
- May need to run: `php artisan migrate:fresh --force`

### Need to view logs
- Render Dashboard → Your Service → "Logs"
- Scroll to see application output

---

## Important Notes

⚠️ **Free Tier Limitations:**
- Services spin down after 15 minutes of inactivity (cold start)
- 0.5 GB RAM may be tight with all features
- Database has 5 GB storage limit
- No custom domain without upgrade

✅ **Best Practices:**
- Keep sensitive data in environment variables
- Enable HTTPS (automatic)
- Monitor database size
- Regular backups recommended for production

---

## Next Steps

1. **Monitor Performance** - Watch CPU/memory in Render dashboard
2. **Add Custom Domain** - Optional (paid feature)
3. **Enable Auto-Deploy** - Already enabled via GitHub integration
4. **Set Up Monitoring** - Use Render's built-in alerts
5. **Populate Sample Data** - Use admin panel to add test data

---

## Support

- **Render Docs:** https://render.com/docs
- **Laravel Docs:** https://laravel.com/docs
- **Issue?** Check Render logs and verify environment variables

---

**Deployment Status: Ready for Live!** 🚀
