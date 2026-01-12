# Quick Deployment Checklist

## ✅ Pre-Deployment Checklist

- [ ] Set up cloud MySQL database (PlanetScale, Railway, etc.)
- [ ] Export local database: `mysqldump -u root codexmlt_shimla_airlines > database_backup.sql`
- [ ] Import database to cloud provider
- [ ] Test database connection from external source
- [ ] Backup original config: `cp includes/config.php includes/config.local.php`
- [ ] Update config to use environment variables
- [ ] Install Vercel CLI: `npm install -g vercel` (optional)
- [ ] Create Vercel account at vercel.com

## 🚀 Deployment Commands

### Option 1: Deploy via Vercel Dashboard
1. Push code to GitHub/GitLab/Bitbucket
2. Go to https://vercel.com/new
3. Import your repository
4. Click Deploy

### Option 2: Deploy via CLI
```bash
# Login to Vercel
vercel login

# Deploy (from project directory)
cd c:\xampp\htdocs\git1
vercel

# For production deployment
vercel --prod
```

## ⚙️ Environment Variables to Set in Vercel

After deployment, go to: **Project Settings → Environment Variables**

Add these variables:
```
DB_HOST=your-database-host
DB_PORT=3306
DB_NAME=your-database-name
DB_USER=your-database-user
DB_PASS=your-database-password
SITE_URL=https://your-app.vercel.app
```

## 🧪 Post-Deployment Testing

- [ ] Visit your Vercel URL
- [ ] Test homepage loads
- [ ] Test database connection (check tours/vehicles pages)
- [ ] Test form submissions
- [ ] Test admin login
- [ ] Test image loading
- [ ] Test booking functionality
- [ ] Check browser console for errors
- [ ] Test on mobile devices

## 🔧 Common Issues & Quick Fixes

### Database Connection Error
```
Check: Environment variables are set correctly in Vercel
Check: Database allows connections from any IP (0.0.0.0/0)
Check: Database credentials are correct
```

### Images Not Loading
```
Check: Image paths are relative, not absolute
Check: Images are committed to git repository
Check: .vercelignore doesn't exclude image directories
```

### PHP Errors
```
Check: PHP version compatibility (Vercel uses PHP 7.4)
Check: Vercel deployment logs for specific errors
Check: File paths use forward slashes (/)
```

## 📱 Custom Domain (Optional)

1. Go to Project Settings → Domains
2. Add your custom domain
3. Update DNS records as instructed
4. Update SITE_URL environment variable

## 🔄 Redeployment

After making changes:
```bash
# Push to git (if using dashboard deployment)
git add .
git commit -m "Update application"
git push

# Or redeploy via CLI
vercel --prod
```

## 📊 Monitor Deployment

- View logs: https://vercel.com/dashboard → Your Project → Deployments
- Check analytics: Project → Analytics
- Monitor errors: Project → Logs

## ⚠️ Important Notes

1. **Vercel has limited PHP support** - consider traditional hosting for complex PHP apps
2. **File uploads won't work** - Vercel filesystem is read-only (use cloud storage)
3. **Sessions may not persist** - use database-backed sessions
4. **Cold starts** - first request may be slow on free tier

## 📚 Resources

- Vercel Docs: https://vercel.com/docs
- PHP on Vercel: https://github.com/vercel-community/php
- PlanetScale: https://planetscale.com/docs
- Railway: https://docs.railway.app

---

**Need Help?** Check `VERCEL_DEPLOYMENT.md` for detailed instructions.
