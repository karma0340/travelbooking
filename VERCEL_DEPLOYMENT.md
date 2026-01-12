# Vercel Deployment Guide for Shimla Air Lines

## Important Notes

⚠️ **PHP on Vercel Limitations:**
- Vercel has **limited PHP support** through serverless functions
- PHP runtime is community-maintained (vercel-php)
- **MySQL connections may have issues** due to serverless nature
- **Recommended:** Consider using a managed MySQL database (PlanetScale, Railway, or Vercel Postgres with PHP PDO)

## Prerequisites

1. **Vercel Account**: Sign up at [vercel.com](https://vercel.com)
2. **Vercel CLI** (optional): `npm install -g vercel`
3. **Remote MySQL Database**: You'll need a cloud-hosted MySQL database since Vercel doesn't provide MySQL

## Recommended Database Options

### Option 1: PlanetScale (Recommended)
- Free tier available
- MySQL-compatible serverless database
- Sign up at [planetscale.com](https://planetscale.com)
- Get connection details (host, username, password, database name)

### Option 2: Railway
- Free tier with $5 credit
- Supports MySQL
- Sign up at [railway.app](https://railway.app)

### Option 3: AWS RDS / Google Cloud SQL
- More expensive but production-ready
- Requires cloud provider account

## Deployment Steps

### Step 1: Prepare Your Database

1. Create a MySQL database on your chosen provider
2. Export your local database:
   ```bash
   mysqldump -u root codexmlt_shimla_airlines > database_backup.sql
   ```
3. Import to your cloud database:
   ```bash
   mysql -h YOUR_HOST -u YOUR_USER -p YOUR_DATABASE < database_backup.sql
   ```

### Step 2: Update Configuration

Replace `includes/config.php` with `includes/config.production.php`:

```bash
# Backup original config
cp includes/config.php includes/config.local.php

# Use production config
cp includes/config.production.php includes/config.php
```

Or modify your existing `includes/config.php` to use environment variables.

### Step 3: Deploy to Vercel

#### Option A: Deploy via Vercel Dashboard (Easiest)

1. Go to [vercel.com/new](https://vercel.com/new)
2. Import your Git repository (GitHub, GitLab, or Bitbucket)
3. Vercel will auto-detect the project
4. Click "Deploy"

#### Option B: Deploy via Vercel CLI

1. Install Vercel CLI:
   ```bash
   npm install -g vercel
   ```

2. Login to Vercel:
   ```bash
   vercel login
   ```

3. Deploy from your project directory:
   ```bash
   cd c:\xampp\htdocs\git1
   vercel
   ```

4. Follow the prompts:
   - Set up and deploy? **Y**
   - Which scope? Select your account
   - Link to existing project? **N**
   - Project name? **shimla-airlines** (or your preferred name)
   - Directory? **./** (current directory)
   - Override settings? **N**

### Step 4: Configure Environment Variables

After deployment, add environment variables in Vercel Dashboard:

1. Go to your project in Vercel Dashboard
2. Navigate to **Settings** → **Environment Variables**
3. Add the following variables:

| Variable Name | Value | Example |
|--------------|-------|---------|
| `DB_HOST` | Your database host | `aws.connect.psdb.cloud` |
| `DB_PORT` | Database port | `3306` |
| `DB_NAME` | Database name | `shimla_airlines` |
| `DB_USER` | Database username | `your_username` |
| `DB_PASS` | Database password | `your_password` |
| `SITE_URL` | Your Vercel URL | `https://shimla-airlines.vercel.app` |

4. Click **Save**
5. Redeploy your application for changes to take effect

### Step 5: Verify Deployment

1. Visit your Vercel URL (e.g., `https://shimla-airlines.vercel.app`)
2. Test the following:
   - Homepage loads correctly
   - Database connection works
   - Forms submit properly
   - Admin panel is accessible
   - Images and CSS load correctly

## Troubleshooting

### Issue: Database Connection Fails

**Solution:**
- Verify environment variables are set correctly
- Check if your database allows connections from Vercel IPs
- Enable SSL for database connections if required
- Check database credentials

### Issue: PHP Version Errors

**Solution:**
- Vercel PHP runtime uses PHP 7.4 by default
- Ensure your code is compatible with PHP 7.4
- Check for deprecated functions

### Issue: File Upload Not Working

**Solution:**
- Vercel filesystem is **read-only**
- Use cloud storage for uploads (AWS S3, Cloudinary, etc.)
- Modify upload logic to use external storage

### Issue: Sessions Not Persisting

**Solution:**
- Serverless functions are stateless
- Use database-backed sessions or JWT tokens
- Consider Redis for session storage

## Alternative: Traditional Hosting

If Vercel proves too limiting for your PHP application, consider:

1. **Shared Hosting**: Hostinger, Bluehost, SiteGround
2. **VPS**: DigitalOcean, Linode, Vultr
3. **Cloud Platforms**: AWS EC2, Google Cloud Compute Engine
4. **PHP-Specific**: Heroku with PHP buildpack, Platform.sh

## Files Created for Deployment

- ✅ `vercel.json` - Vercel configuration
- ✅ `.vercelignore` - Files to exclude from deployment
- ✅ `includes/config.production.php` - Production configuration with env vars
- ✅ `VERCEL_DEPLOYMENT.md` - This deployment guide

## Next Steps

1. Set up a cloud MySQL database
2. Update environment variables in Vercel
3. Test thoroughly after deployment
4. Set up custom domain (optional)
5. Configure SSL certificate (automatic with Vercel)

## Support

For issues specific to:
- **Vercel**: [vercel.com/docs](https://vercel.com/docs)
- **PHP on Vercel**: [github.com/vercel-community/php](https://github.com/vercel-community/php)
- **Database**: Refer to your database provider's documentation

---

**Note:** Due to PHP's traditional server-based nature and Vercel's serverless architecture, you may encounter limitations. For a production PHP application, traditional hosting or a PHP-optimized platform might be more suitable.
