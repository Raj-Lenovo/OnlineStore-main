# Admin Login Fix Guide

## Problem
Cannot login with admin credentials (username: admin, password: admin123)

## Solutions

### Method 1: Use the PHP Fix Script (Easiest)

1. **Open your browser and go to:**
   ```
   http://localhost/OnlineStore/fix_admin_password.php
   ```

2. **The script will:**
   - Generate the correct password hash
   - Update the admin password in the database
   - Verify the password works

3. **After it works:**
   - **DELETE** the file `fix_admin_password.php` for security
   - Try logging in at `http://localhost/OnlineStore/admin/login.php`

---

### Method 2: Use SQL Update (phpMyAdmin)

1. **Open phpMyAdmin**
   - Go to `http://localhost/phpmyadmin`
   - Select the `online_store` database

2. **Run the SQL:**
   - Click on the "SQL" tab
   - Copy and paste the contents of `fix_admin_password.sql`
   - Click "Go"

3. **Or manually update:**
   ```sql
   USE online_store;
   
   UPDATE users 
   SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
   WHERE username = 'admin';
   ```

---

### Method 3: Generate New Hash (If above doesn't work)

1. **Create a temporary PHP file** (e.g., `generate_hash.php`):
   ```php
   <?php
   echo password_hash('admin123', PASSWORD_DEFAULT);
   ?>
   ```

2. **Run it in browser:**
   ```
   http://localhost/OnlineStore/generate_hash.php
   ```

3. **Copy the generated hash** and update in phpMyAdmin:
   ```sql
   UPDATE users 
   SET password = 'PASTE_GENERATED_HASH_HERE' 
   WHERE username = 'admin';
   ```

4. **Delete the temporary file** after use

---

### Method 4: Create New Admin User

If nothing works, create a new admin user:

```sql
USE online_store;

INSERT INTO users (username, email, password, full_name, role) 
VALUES (
    'admin', 
    'admin@store.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Administrator', 
    'admin'
) 
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    role = 'admin';
```

---

## Verify Admin User

After fixing, verify the admin user exists:

```sql
SELECT id, username, email, role FROM users WHERE username = 'admin';
```

Should show:
- username: admin
- role: admin

---

## Default Credentials

- **Username:** `admin`
- **Password:** `admin123`

⚠️ **Change the password after first login!**

---

## Troubleshooting

### Still can't login?

1. **Check database connection:**
   - Verify `includes/config.php` has correct database credentials
   - Default: host='localhost', user='root', password='', db='online_store'

2. **Check if admin user exists:**
   ```sql
   SELECT * FROM users WHERE username = 'admin';
   ```

3. **Check user role:**
   ```sql
   SELECT username, role FROM users WHERE username = 'admin';
   ```
   Role must be 'admin' (not 'user')

4. **Clear browser cache/cookies:**
   - Sometimes session issues can cause problems
   - Try incognito/private browsing mode

5. **Check for typos:**
   - Username: `admin` (lowercase, no spaces)
   - Password: `admin123` (no spaces)

---

## Security Note

After fixing the password:
- ✅ Delete `fix_admin_password.php`
- ✅ Delete `generate_hash.php` (if created)
- ✅ Change the default password to something secure
- ✅ Consider using a strong password

---

## Need Help?

If none of these methods work:
1. Check PHP error logs
2. Verify database is running
3. Check file permissions
4. Ensure all SQL files were imported correctly

