# WaterProof CRM - Enterprise Module Setup & Deployment Guide

## Project Overview
A comprehensive Enterprise-Level User Management & Authentication Module for WaterProof CRM with advanced RBAC, activity tracking, and premium admin panel UI.

## Current Status: ✅ COMPLETE

### Architecture Completed
- ✅ Models (User, Role, Permission, LoginHistory, ActivityLog)
- ✅ Controllers (Auth, Dashboard, Users, Roles, ActivityLogs)
- ✅ Services (AuthService, UserService)
- ✅ Middleware (Auth, Permissions, LogActivity, SessionTimeout)
- ✅ Migrations (Users, Roles, Permissions, LoginHistories, ActivityLogs)
- ✅ Blade Views (All admin panels, auth pages, user management)
- ✅ Routes (Auth, Admin dashboard, CRUD operations)
- ✅ Seeders (Create roles, permissions, sample users)

---

## Installation & Setup Instructions

### 1. Environment Configuration
```bash
cd c:\xampp\htdocs\CRM

# Update .env file
cp .env.example .env
```

**Set these values in .env:**
```
APP_NAME="WaterProof CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

DB_DATABASE=waterproof_crm
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=noreply@waterproofcrm.com
```

### 2. Install Dependencies
```bash
# Install Composer packages
composer install

# Generate app key
php artisan key:generate

# Install npm packages
npm install

# Build assets
npm run build
```

### 3. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed the database (creates roles, permissions, and sample users)
php artisan db:seed

# Create storage symlink for avatars
php artisan storage:link
```

### 4. Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 5. Start Development Server
```bash
# Run Laravel development server
php artisan serve

# In another terminal, run npm dev server (for real-time asset compilation)
npm run dev
```

Access the application at: `http://localhost:8000`

---

## Default Login Credentials

### Super Admin
- **Email:** superadmin@waterproofcrm.com
- **Password:** Admin@123

### Admin
- **Email:** admin@waterproofcrm.com
- **Password:** Admin@123

### Sample Users
1. **Manager:** manager@waterproofcrm.com
2. **Sales Executive:** sales@waterproofcrm.com
3. **Accountant:** accounts@waterproofcrm.com
4. **Site Engineer:** engineer@waterproofcrm.com
5. **All Passwords:** Admin@123

---

## Key Features Implemented

### Authentication Module
✅ Login with email/mobile  
✅ Forgot password with email reset  
✅ Remember me functionality  
✅ Session timeout handling  
✅ Failed login attempt tracking  
✅ Account lock after 5 failed attempts  
✅ Last login tracking  
✅ Browser & device detection  
✅ IP address logging  

### User Management Module
✅ Create/Edit/Delete users  
✅ Bulk delete users  
✅ Bulk status update  
✅ Search & filter users  
✅ Profile image upload  
✅ Reset user password  
✅ Force logout from all devices  
✅ View login history  
✅ View activity logs  
✅ User status management (active/inactive/suspended)  

### Role & Permission System
✅ 6 predefined roles (Super Admin, Admin, Manager, Sales Executive, Accountant, Site Engineer)  
✅ 27 granular permissions organized by module  
✅ Dynamic permission assignment  
✅ Role-based dashboard  
✅ Module-wise access control  

### Dashboard
✅ User statistics (total, active, inactive, suspended)  
✅ User registration trend chart  
✅ Recent users list  
✅ Recent activities  
✅ Recent login history  

### Admin Panel Design
✅ Modern responsive design  
✅ Dark mode ready  
✅ Sticky header  
✅ Collapsible sidebar  
✅ Smooth animations  
✅ Bootstrap 5 styled  
✅ Mobile optimized  

### Activity Logging
✅ Log all user actions  
✅ Track IP address & browser  
✅ Record device type  
✅ Store action details  
✅ View activity history  

---

## File Structure

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── ActivityLogController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── RoleController.php
│   │   │   └── UserController.php
│   │   ├── Auth/
│   │   │   ├── ForgotPasswordController.php
│   │   │   └── LoginController.php
│   │   └── Controller.php
│   ├── Middleware/
│   │   ├── Authenticate.php
│   │   ├── CheckPermission.php
│   │   ├── LogActivity.php
│   │   └── SessionTimeout.php
│   └── Kernel.php
├── Models/
│   ├── ActivityLog.php
│   ├── LoginHistory.php
│   ├── Permission.php
│   ├── Role.php
│   └── User.php
├── Providers/
├── Services/
│   ├── AuthService.php
│   └── UserService.php
└── ...

database/
├── migrations/
│   ├── 2014_10_12_000000_create_users_table.php
│   ├── 2024_01_01_000001_create_roles_table.php
│   ├── 2024_01_01_000002_create_permissions_table.php
│   ├── 2024_01_01_000003_create_login_histories_table.php
│   ├── 2024_01_01_000004_create_activity_logs_table.php
│   └── 2024_01_01_000005_create_notifications_table.php
└── seeders/
    └── DatabaseSeeder.php

resources/views/
├── layouts/
│   ├── app.blade.php (main admin layout)
│   └── auth.blade.php (auth pages layout)
├── auth/
│   ├── login.blade.php
│   ├── forgot-password.blade.php
│   └── reset-password.blade.php
└── admin/
    ├── dashboard.blade.php
    ├── users/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── show.blade.php
    ├── roles/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    └── activity-logs/
        └── index.blade.php

routes/
├── web.php (all application routes)
└── api.php (API routes if needed)
```

---

## API Endpoints

### Authentication
- `POST /login` - User login
- `POST /logout` - User logout
- `GET /forgot-password` - Forgot password form
- `POST /forgot-password` - Send reset email
- `GET /reset-password/{token}` - Reset password form
- `POST /reset-password` - Submit new password

### Admin Dashboard
- `GET /admin/dashboard` - Dashboard view

### User Management
- `GET /admin/users` - List users with filtering
- `GET /admin/users/create` - Create user form
- `POST /admin/users` - Store new user
- `GET /admin/users/{user}` - View user details
- `GET /admin/users/{user}/edit` - Edit user form
- `PUT /admin/users/{user}` - Update user
- `DELETE /admin/users/{user}` - Delete user
- `POST /admin/users/bulk-action` - Bulk operations
- `POST /admin/users/{user}/status` - Update user status
- `POST /admin/users/{user}/reset-password` - Reset password
- `POST /admin/users/{user}/force-logout` - Force logout

### Role Management
- `GET /admin/roles` - List roles
- `GET /admin/roles/create` - Create role form
- `POST /admin/roles` - Store new role
- `GET /admin/roles/{role}/edit` - Edit role form
- `PUT /admin/roles/{role}` - Update role
- `DELETE /admin/roles/{role}` - Delete role

### Activity Logs
- `GET /admin/activity-logs` - View activity logs
- `GET /admin/login-history` - View login history

---

## Security Features Implemented

✅ CSRF Protection (VerifyCsrfToken middleware)  
✅ XSS Protection (Blade auto-escaping)  
✅ SQL Injection Prevention (Eloquent ORM)  
✅ Password Hashing (bcrypt)  
✅ Route Protection (Auth middleware)  
✅ Permission Middleware (CheckPermission)  
✅ Session Security (Session timeout after inactivity)  
✅ Rate Limiting (Throttle middleware on login)  
✅ Account Lockout (After 5 failed login attempts)  
✅ Secure Password Reset (Email token verification)  
✅ Activity Audit Logging  
✅ Soft Deletes (for data recovery)  

---

## Middleware Details

### Authenticate Middleware
- Redirects unauthenticated users to login
- Protects admin routes

### CheckPermission Middleware
- Verifies user has required permission
- Returns 403 if not authorized
- Usage: `middleware('permission:users.create')`

### LogActivity Middleware
- Logs all admin panel activities
- Stores IP address, user agent, action details
- Applied to all admin routes

### SessionTimeout Middleware
- Auto-logout after 30 minutes of inactivity
- Configurable timeout duration
- Shows warning before timeout

---

## Configuration

### Auth Config (config/auth.php)
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

### Session Timeout
Set in `.env`:
```
SESSION_TIMEOUT_MINUTES=30
```

### Max Login Attempts
Set in `config/auth.php`:
```php
'max_attempts' => 5,
'lockout_minutes' => 15,
```

---

## Database Relationships

```
User
├── has many LoginHistory
├── has many ActivityLog
└── belongs to Role

Role
├── has many Users
└── has many Permissions (through RolePermissions)

Permission
├── has many Roles (through RolePermissions)
└── has many Users (through UserPermissions)

LoginHistory
└── belongs to User

ActivityLog
└── belongs to User
```

---

## Customization Guide

### Add New Permission
1. Edit `database/seeders/DatabaseSeeder.php`
2. Add permission to `$permissions` array
3. Run: `php artisan migrate:refresh --seed`

### Add New Role
1. Create in database seeder or use admin panel
2. Assign permissions via admin panel
3. Users can be assigned the role

### Customize Dashboard
- Edit `resources/views/admin/dashboard.blade.php`
- Modify `DashboardController@index` for data

### Customize Sidebar
- Edit layout in `resources/views/layouts/app.blade.php`
- Use `auth()->user()->hasPermission('permission.name')` to conditionally show menu items

---

## Troubleshooting

### Permission Denied Errors
- Check user role and assigned permissions
- Verify permission middleware is applied to route
- Check `hasPermission()` method in User model

### Session Timeout Issues
- Verify `SessionTimeout` middleware is in web group
- Check `SESSION_TIMEOUT_MINUTES` in .env
- Clear sessions: `php artisan cache:clear`

### Avatar Upload Issues
- Verify storage symlink: `php artisan storage:link`
- Check storage permissions: `chmod -R 775 storage`
- Verify `disk` config in `config/filesystems.php`

### Migration Issues
- Clear migrations: `php artisan migrate:reset`
- Rollback & retry: `php artisan migrate:rollback && php artisan migrate --seed`

---

## Performance Optimization

### Database Queries
- Eager loading used (`->with()`) to prevent N+1 queries
- Pagination implemented on list views
- Indexes on frequently queried fields

### Caching
- Cache dashboard statistics
- Implement Redis for session storage
- Cache permission checks

### Assets
- Use `npm run build` for production
- Enable gzip compression in web server
- Minify CSS/JS

---

## Testing

### Test Users (Use for Development)
All test users have password: `Admin@123`

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

---

## Deployment Checklist

- [ ] Update .env with production values
- [ ] Set `APP_DEBUG=false`
- [ ] Run migrations on production database
- [ ] Run seeders for initial data
- [ ] Create storage symlink: `php artisan storage:link`
- [ ] Set correct file permissions (775 for storage, bootstrap/cache)
- [ ] Configure email service (SMTP settings)
- [ ] Set up backup strategy
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up monitoring & logging
- [ ] Test all authentication flows
- [ ] Verify permission enforcement

---

## Support & Maintenance

### Regular Maintenance
- Monitor activity logs for suspicious activities
- Review failed login attempts
- Backup database regularly
- Update dependencies: `composer update`
- Clear old session files periodically

### Monitoring
- Watch for repeated failed login attempts
- Monitor user creation/deletion activities
- Track permission changes
- Review database performance

---

## Additional Features (Ready for Extension)

The architecture supports easy addition of:
- Two-factor authentication (2FA)
- OAuth/SSO integration
- Email notifications
- SMS alerts
- API tokens for mobile apps
- Advanced reporting
- Data export (Excel/PDF)
- Webhook integrations
- Multi-tenancy support

---

## License
This project is licensed under the MIT License.

---

## Last Updated
Generated: May 18, 2026
Status: Production Ready ✅
