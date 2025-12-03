# Admin Login Page Not Found - Fix Guide

## Problem
Getting "404 Not Found" or "Page not found" when accessing:
```
http://localhost/OnlineStore/admin/login.php
```

## Quick Fixes

### 1. Check XAMPP is Running
- Open XAMPP Control Panel
- Make sure **Apache** is running (green/started)
- If not, click "Start" next to Apache

### 2. Verify Correct URL

Try these URLs in order:

**Option A: With /OnlineStore/**
```
http://localhost/OnlineStore/admin/login.php
```

**Option B: Direct path (if OnlineStore is in htdocs root)**
```
http://localhost/admin/login.php
```

**Option C: Check your actual folder name**
- Your folder might be named differently
- Check: `C:\xampp\htdocs\` - what's the exact folder name?
- Use that name in the URL

### 3. Test Access First

1. **Open this test file:**
   ```
   http://localhost/OnlineStore/test_access.php
   ```
   This will show you:
   - If PHP is working
   - Correct file paths
   - Which URLs to use

2. **Try the main store first:**
   ```
   http://localhost/OnlineStore/index.php
   ```
   or
   ```
   http://localhost/OnlineStore/
   ```

### 4. Check File Exists

The file should be at:
```
C:\xampp\htdocs\OnlineStore\admin\login.php
```

Verify it exists:
- Open File Explorer
- Navigate to: `C:\xampp\htdocs\OnlineStore\admin\`
- Look for `login.php`

### 5. Alternative Access Methods

**Method 1: Access via Main Store**
1. Go to: `http://localhost/OnlineStore/`
2. Click "Login" (top right)
3. Login with admin credentials
4. If you're admin, you'll see "Admin Panel" in the dropdown menu

**Method 2: Direct File Access**
If the URL doesn't work, try accessing via:
```
http://localhost/OnlineStore/admin/
```
Then click on `login.php` if it shows in directory listing

**Method 3: Use IP Address**
```
http://127.0.0.1/OnlineStore/admin/login.php
```

### 6. Check Apache Configuration

If nothing works, check Apache:

1. **Open:** `C:\xampp\apache\conf\httpd.conf`

2. **Find this line:**
   ```apache
   DocumentRoot "C:/xampp/htdocs"
   ```

3. **Make sure it points to your XAMPP htdocs folder**

4. **Restart Apache** in XAMPP Control Panel

### 7. Check .htaccess (if exists)

If you have a `.htaccess` file, it might be blocking access. Check:
- `C:\xampp\htdocs\OnlineStore\.htaccess`
- `C:\xampp\htdocs\OnlineStore\admin\.htaccess`

Temporarily rename them to test:
- `.htaccess` → `.htaccess.bak`

### 8. Common Issues

**Issue: Folder name mismatch**
- Your folder might be: `OnlineStore`, `onlinestore`, `Online-Store`, etc.
- Use the EXACT folder name in the URL (case-sensitive on Linux, not on Windows)

**Issue: Port number**
- If Apache is on a different port (like 8080), use:
  ```
  http://localhost:8080/OnlineStore/admin/login.php
  ```

**Issue: Virtual Host**
- If you set up a virtual host, use that domain instead

### 9. Verify PHP is Working

Create a test file `test.php` in your project root:
```php
<?php
phpinfo();
?>
```

Access: `http://localhost/OnlineStore/test.php`

If this doesn't work, PHP/Apache isn't configured correctly.

### 10. Check Error Logs

Check Apache error log:
- Location: `C:\xampp\apache\logs\error.log`
- Look for any errors related to your request

---

## Step-by-Step Troubleshooting

1. ✅ **XAMPP Control Panel** → Apache is running
2. ✅ **Test main page:** `http://localhost/OnlineStore/`
3. ✅ **Test file exists:** Check `C:\xampp\htdocs\OnlineStore\admin\login.php`
4. ✅ **Try test file:** `http://localhost/OnlineStore/test_access.php`
5. ✅ **Check folder name:** Verify exact name in `C:\xampp\htdocs\`
6. ✅ **Try different URL formats** (see above)

---

## Still Not Working?

### Create a Simple Redirect

Create `admin/index.php`:
```php
<?php
header('Location: login.php');
exit;
?>
```

Then try: `http://localhost/OnlineStore/admin/`

### Direct Access Test

Create `admin/test.php`:
```php
<?php
echo "Admin folder is accessible!";
echo "<br><a href='login.php'>Go to Login</a>";
?>
```

Access: `http://localhost/OnlineStore/admin/test.php`

---

## Quick Verification Checklist

- [ ] XAMPP Apache is running
- [ ] File exists at: `C:\xampp\htdocs\OnlineStore\admin\login.php`
- [ ] Main store works: `http://localhost/OnlineStore/`
- [ ] Test file works: `http://localhost/OnlineStore/test_access.php`
- [ ] No .htaccess blocking access
- [ ] Correct folder name in URL
- [ ] No port number needed (or correct port)

---

## Alternative: Access via User Login

If admin login page still doesn't work:

1. **Go to main store:** `http://localhost/OnlineStore/`
2. **Click "Login"** (top right)
3. **Login with:**
   - Username: `admin`
   - Password: `admin123`
4. **After login, click your username** (top right)
5. **Select "Admin Panel"** from dropdown

This will take you to the admin dashboard directly!

---

**Need more help?** Check the error message in your browser - it often gives clues about what's wrong!

