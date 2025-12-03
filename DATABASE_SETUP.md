# Complete Database Setup Guide

## 🚀 Quick Setup (One File Only!)

### Step 1: Import the Complete Database

1. **Open phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Import the file:**
   - Click on "Import" tab (top menu)
   - Click "Choose File"
   - Select: `complete_database.sql`
   - Click "Go" button

3. **Done!** Your entire database is now set up with:
   - ✅ All tables (users, products, orders, cart, reviews, etc.)
   - ✅ Admin user account
   - ✅ Sample categories
   - ✅ 10 sample products
   - ✅ 5 test user accounts
   - ✅ 17 sample reviews

---

## 📋 What's Included

### Tables Created:
1. **users** - User accounts (customers and admin)
2. **categories** - Product categories
3. **products** - Product catalog
4. **cart** - Shopping cart items
5. **orders** - Customer orders
6. **order_items** - Order line items
7. **reviews** - Product reviews and ratings

### Sample Data:
- **1 Admin User:**
  - Username: `admin`
  - Password: `admin123`
  - Email: `admin@store.com`

- **5 Test Users:**
  - john_doe, jane_smith, mike_wilson, sarah_jones, david_brown
  - All passwords: `admin123`

- **6 Categories:**
  - Laptops, Desktops, Monitors, Keyboards, Mice, Accessories

- **10 Products:**
  - Gaming Laptop Pro ($1,299.99)
  - Business Laptop ($899.99)
  - Gaming Desktop ($1,599.99)
  - Office Desktop ($599.99)
  - 4K Monitor 27" ($399.99)
  - Gaming Monitor 24" ($249.99)
  - Mechanical Keyboard ($129.99)
  - Wireless Mouse ($49.99)
  - Gaming Mouse ($79.99)
  - USB-C Hub ($39.99)

- **17 Reviews:**
  - Products 1, 2, 3, 5, 7, 9 have reviews
  - Products 4, 6, 8, 10 are left for users to review first

---

## 🔐 Default Credentials

### Admin Login:
- **URL:** `http://localhost/OnlineStore/admin/login.php`
- **Username:** `admin`
- **Password:** `admin123`

### Test User Login:
- **URL:** `http://localhost/OnlineStore/login.php`
- **Username:** Any of: `john_doe`, `jane_smith`, `mike_wilson`, `sarah_jones`, `david_brown`
- **Password:** `admin123`

⚠️ **IMPORTANT:** Change the admin password after first login!

---

## 🗑️ If You Need to Start Fresh

### Option 1: Drop and Recreate
```sql
DROP DATABASE IF EXISTS online_store;
```
Then import `complete_database.sql` again.

### Option 2: Clear All Data
```sql
USE online_store;
TRUNCATE TABLE reviews;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE cart;
TRUNCATE TABLE products;
TRUNCATE TABLE categories;
DELETE FROM users WHERE username != 'admin';
```
Then re-import `complete_database.sql` or run the INSERT statements again.

---

## ✅ Verification

After importing, verify everything works:

1. **Check Admin Login:**
   - Go to: `http://localhost/OnlineStore/admin/login.php`
   - Login with: `admin` / `admin123`
   - Should redirect to dashboard

2. **Check Products:**
   - Go to: `http://localhost/OnlineStore/products.php`
   - Should see 10 products

3. **Check Reviews:**
   - Go to any product detail page
   - Products 1, 2, 3, 5, 7, 9 should have reviews
   - Products 4, 6, 8, 10 should show "No reviews yet"

4. **Check Admin Dashboard:**
   - Should show statistics:
     - Total Products: 10
     - Total Users: 6 (1 admin + 5 test users)
     - Total Orders: 0 (initially)
     - Total Revenue: $0.00 (initially)

---

## 📝 Notes

- All password hashes use: `password_hash('admin123', PASSWORD_DEFAULT)`
- Reviews are distributed across different products
- Some products intentionally have no reviews (for testing)
- All tables use UTF8MB4 encoding for full Unicode support
- Foreign keys ensure data integrity

---

## 🆘 Troubleshooting

### "Database already exists" error:
- Either drop the existing database first, or
- The file uses `CREATE DATABASE IF NOT EXISTS` so it's safe to run again

### "Duplicate entry" errors:
- The file uses `ON DUPLICATE KEY UPDATE` so it's safe to run multiple times
- Existing data won't be duplicated

### "Foreign key constraint fails":
- Make sure you're running the entire file in order
- Don't skip any sections

---

## 📦 File Information

- **File:** `complete_database.sql`
- **Size:** ~8KB
- **Encoding:** UTF-8
- **Compatible with:** MySQL 5.7+, MariaDB 10.2+

---

**That's it! One file, complete setup! 🎉**

