# WebForge: SaaS Commercial Website Builder

WebForge is a commercial-grade SaaS Website Builder similar to Wix, squarespace, and Durable AI. It allows developers, agencies, and businesses to deploy drag-and-drop website canvases, generate full content matching dynamic niches automatically using local heuristic AIs, and manage plans with payments.

Built with an elegant, framework-free Object-Oriented PHP 8.3 MVC architecture, it is fully compatible with local XAMPP setups and Shared Hosting providers like Hostinger.

---

## 🚀 Key Modules & System Features

1. **Interactive Builder Canvas**: A drag-and-drop builder utilizing Bootstrap 5, SortableJS, and jQuery. Supports inline content updates (`contenteditable`), grid section reordering, and element style customizations (gradients, padding, colors, fonts, border radius).
2. **Local Heuristic AI Generator**: A wizard that parses inputs (business name, color themes, category, description) to compile custom website pages, service descriptions, pricing grids, dynamic reviews, custom FAQs, contact relays, and automated SEO metadata.
3. **Template Marketplace**: Seeds a directory of **50 professional niche layouts** (Business, Gyms, Restaurants, Consulting, Lawyers, Doctors, real estate, etc.) dynamically customizable by users.
4. **Platform Control Panel (Super Admin)**: Statistics dashboards (subscribers count, hosted site listings, gross revenue tracking), user status flags (suspend/activate), marketplace templates configuration, and global keys settings.
5. **Secure Authentication & Verification**: Double-checked credentials validations, email verify routines, password recovery hashes, remember-me token configurations, rate limiting, and activity logs.
6. **Lead Capturing relay (Contact forms)**: Custom forms mapped per project that record inquiries to database, push alerts, and send automated confirmation relays.
7. **Marketing & Analytics Integration**: Connects measurement trackers (Google Analytics, Facebook Pixel tags) and floating WhatsApp support widget links.
8. **Static Exporter**: Compiles active database page layout loops into static, lightweight, fully responsive Bootstrap 5 `.zip` assets, or pushes them direct to GitHub repository main branches.

---

## 📂 Project Structure

```text
/config
  - config.php            # Core constants, DB credentials, session salts
  - database.php          # Secure PDO execution wrappers
/database
  - schema.sql            # Core tables, index definitions, and 50 templates seed
/includes
  - auth.php              # Auth cookie validations & permission middleware
  - functions.php         # Sanitization filters, CSRF fields, rate-limit logs, mails
/controllers
  - Controller.php        # Base MVC controller with extract rendering guides
  - AuthController.php    # Sign In, Sign Up, Verify, Reset password controllers
  - DashboardController.php# User website actions, SMTP keys, and pixel configs
  - BuilderController.php # Drag-and-drop canvas layout compiler & previewer
  - AIController.php      # Local heuristics generative template engine
  - ApiController.php     # REST API endpoints (saves layout, upload media, zip exports, github push)
  - AdminController.php   # Super admin user metrics, plans, templates marketplace
/models
  - User.php              # Security checks, account creator, reset links
  - Website.php           # Project creator, cloner, deletion, and template loaders
  - Subscription.php      # Upgrade subscription tiers & resource storage check
/views
  - auth/                 # Sign In, Sign Up, recovery templates
  - dashboard/            # Workspace lists, SMTP panels, GA embeds
  - admin/                # Users management, templates JSON registers, platforms config
  - builder/              # SortableJS workspace canvas & preview sheets
  - site/                 # Public dynamic compiler loop for visitors
  - includes/             # Shared header & footer layout assets
/uploads
  - media/                # Target folder for user file uploads
index.php                 # Front controller router
```

---

## 🛠️ Local Installation & XAMPP Guide

1. **Download Code**: Copy the WebForge code files into your local server root folder (e.g. `C:\xampp\htdocs\antigravity`).
2. **Create Database**: Open PHPMyAdmin, create a database named `webforge_db` with `utf8mb4_unicode_ci` collations.
3. **Seed Database Schema**: Import and execute the SQL script located at:
   [schema.sql](file:///c:/antigravity/database/schema.sql)
4. **Configure Constants**: Edit [config.php](file:///c:/antigravity/config/config.php) to update base URLs, credentials, or target paths:
   ```php
   define('APP_URL', 'http://localhost/antigravity');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'webforge_db');
   ```
5. **Default Credentials**:
   * **Super Admin**: `admin@builder.local` (Password: `admin123`)
   * **General User**: `john@builder.local` (Password: `admin123`)
6. Run Apache & MySQL in XAMPP, and navigate to: `http://localhost/antigravity`

---

## 🌐 Hostinger Shared Hosting Deployment Guide

1. **Extract Zip**: Log into your Hostinger hPanel, go to File Manager, and upload/extract the website bundle directly into your domain folder (e.g. `/public_html`).
2. **Configure Database**:
   * Create a MySQL Database and Database User under hPanel -> Databases -> MySQL Databases.
   * Note the Host name, DB name, DB user, and Password.
3. **Execute SQL File**:
   * Go to phpMyAdmin, open your newly created database, and import the SQL file: [schema.sql](file:///c:/antigravity/database/schema.sql).
4. **Sync config.php**:
   * Open `/config/config.php` and update:
     * `APP_URL` to your live domain (e.g., `https://mybuilder.com`).
     * `DB_HOST` (usually `localhost` or Hostinger DB IP).
     * `DB_USER` (Hostinger DB user prefix).
     * `DB_PASS` (Hostinger database password).
     * `DB_NAME` (Hostinger database name).
     * Change `APP_ENV` to `production`.
5. **Routing Setup (.htaccess)**:
   * Since this is a framework-free custom MVC, ensure all virtual URL paths route through `index.php`. Create/edit a `.htaccess` file in your root `/public_html` directory:
     ```apache
     <IfModule mod_rewrite.c>
     RewriteEngine On
     RewriteBase /
     
     # Prevent loop on physical files/folders
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteCond %{REQUEST_FILENAME} !-d
     
     # Route everything to front controller index
     RewriteRule ^(.*)$ index.php [L,QSA]
     </IfModule>
     ```
6. **Set Folder Permissions**:
   * Ensure directories `/uploads` and `/uploads/media` are writable. Set permissions to `755` (or `777` if required by Hostinger's system user).

---

## 🐱 GitHub Deploy & Hosting Guide

1. Log into your GitHub account, go to **Settings -> Developer Settings -> Personal Access Tokens (classic)**.
2. Generate a new token with permissions: `repo` (Full control of private repositories) and `workflow` (optional).
3. In WebForge, open your website settings or click "Deploy to GitHub" inside the dashboard.
4. Input the Target Repository Name (e.g. `acme-landing-page`) and paste your classic token.
5. Click **Deploy**. WebForge's [ApiController](file:///c:/antigravity/controllers/ApiController.php#L226) will create the repository on your GitHub account, compile your dynamic database sections to clean static pages, rename the link layouts to relative pages, and push everything as a commit!
6. Go to your repository settings in GitHub, navigate to **Pages**, set build source to **Deploy from branch**, select **main branch / root folder**, and click Save. Your site will be hosted live on `https://username.github.io/acme-landing-page` in under a minute!
