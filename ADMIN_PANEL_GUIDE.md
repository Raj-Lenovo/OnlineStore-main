# Admin Panel Guide

## 📍 Location & Access

### URL
```
http://localhost/OnlineStore/admin/login.php
```

### Default Credentials
- **Username:** `admin`
- **Password:** `admin123`
- ⚠️ **Important:** Change the password after first login!

### Access Requirements
- Must have `admin` role in the database
- Regular users cannot access admin panel (they'll be redirected)

---

## 🎯 Admin Panel Features & Use Cases

### 1. **Admin Dashboard** (`admin/dashboard.php`)

**Location:** Main admin page after login

**Use Cases:**
- **View Store Statistics:**
  - Total Products count
  - Total Orders count
  - Total Users count
  - Total Revenue (sum of all non-cancelled orders)
  
- **Quick Actions:**
  - Add New Product
  - Manage Products
  - View All Orders
  - Manage Reviews
  - View Store (customer-facing site)

- **Recent Orders Overview:**
  - See last 10 orders
  - View order status at a glance
  - Quick access to order details

**When to Use:**
- Daily check-in to monitor store activity
- Quick overview of business metrics
- Starting point for all admin tasks

---

### 2. **Product Management**

#### A. **Add New Product** (`admin/add-product.php`)

**Use Cases:**
- Add new products to the store
- Upload product images
- Set product details:
  - Name, Description
  - Price, Stock quantity
  - Category assignment
  - Product image (upload or enter path)

**When to Use:**
- Adding new inventory
- Launching new products
- Expanding product catalog

**Features:**
- Image upload (JPEG, PNG, GIF, WebP, max 5MB)
- Manual image path entry option
- Category selection
- Stock management

---

#### B. **Manage Products** (`admin/products.php`)

**Use Cases:**
- View all products in a table
- See product details:
  - ID, Name, Category
  - Price, Stock level
- Quick access to edit/delete

**When to Use:**
- Reviewing entire product catalog
- Finding specific products
- Bulk product management

**Actions Available:**
- **Edit:** Modify product details
- **Delete:** Remove products (only if not in orders)

---

#### C. **Edit Product** (`admin/edit-product.php`)

**Use Cases:**
- Update product information
- Change prices
- Update stock levels
- Replace product images
- Modify descriptions

**When to Use:**
- Price adjustments
- Stock updates
- Product information corrections
- Image updates

**Features:**
- Preview current image
- Upload new image (replaces old one)
- Update all product fields
- Maintains product ID

---

#### D. **Delete Product** (`admin/delete-product.php`)

**Use Cases:**
- Remove products from catalog
- Discontinue items

**When to Use:**
- Products no longer available
- Removing outdated items
- Cleaning up catalog

**Safety Features:**
- Cannot delete products with existing orders
- Confirmation required before deletion
- Prevents accidental deletions

---

### 3. **Order Management** (`admin/view-orders.php`)

**Use Cases:**
- View all customer orders
- Update order status:
  - Pending → Processing → Shipped → Delivered
  - Can also cancel orders
- View detailed order information:
  - Customer details
  - Order items
  - Total amount
  - Shipping address
  - Order date

**When to Use:**
- Processing new orders
- Tracking order fulfillment
- Customer service inquiries
- Updating shipping status

**Features:**
- Status dropdown (updates immediately)
- Order details modal
- Customer information display
- Order items breakdown
- Total revenue tracking

**Order Statuses:**
- 🟡 **Pending:** New order, not yet processed
- 🔵 **Processing:** Order being prepared
- 🟣 **Shipped:** Order sent to customer
- 🟢 **Delivered:** Order completed
- 🔴 **Cancelled:** Order cancelled

---

### 4. **Review Management** (`admin/view-reviews.php`)

**Use Cases:**
- View all customer reviews
- Monitor product ratings
- Delete inappropriate reviews
- Moderate customer feedback

**When to Use:**
- Reviewing customer feedback
- Removing spam/inappropriate content
- Monitoring product reputation
- Quality control

**Features:**
- View all reviews in one place
- See product, user, rating, and comment
- Delete reviews with confirmation
- Link to product page

**Information Displayed:**
- Review ID
- Product name (with link)
- Reviewer name
- Star rating (1-5)
- Review comment
- Review date

---

## 🔐 Security Features

1. **Role-Based Access:**
   - Only users with `admin` role can access
   - Regular users are blocked

2. **Session Protection:**
   - All admin pages require login
   - Auto-redirect if not logged in

3. **Input Validation:**
   - Prepared statements prevent SQL injection
   - File upload validation
   - Input sanitization

---

## 📊 Typical Admin Workflow

### Daily Tasks:
1. **Morning Check:**
   - Login to dashboard
   - Check new orders
   - Review statistics

2. **Order Processing:**
   - View new orders
   - Update status to "Processing"
   - Prepare items
   - Update to "Shipped" when sent
   - Mark "Delivered" when confirmed

3. **Product Management:**
   - Add new products as needed
   - Update stock levels
   - Adjust prices
   - Update product images

4. **Review Moderation:**
   - Check new reviews
   - Remove inappropriate content
   - Monitor ratings

### Weekly Tasks:
- Review overall statistics
- Check revenue trends
- Manage product catalog
- Review customer feedback

---

## 🚀 Quick Access Links

From the main store, admins can:
- Click on username dropdown → "Admin Panel"
- Or directly visit: `http://localhost/OnlineStore/admin/login.php`

From admin dashboard:
- All major functions accessible via "Quick Actions"
- Recent orders visible on dashboard
- Easy navigation between sections

---

## 💡 Tips & Best Practices

1. **Regular Backups:**
   - Backup database before major changes
   - Keep product images backed up

2. **Order Management:**
   - Update order status promptly
   - Keep customers informed

3. **Product Updates:**
   - Keep stock levels accurate
   - Update prices regularly
   - Use quality product images

4. **Review Moderation:**
   - Check reviews regularly
   - Respond to negative feedback appropriately
   - Maintain review quality

5. **Security:**
   - Change default admin password
   - Use strong passwords
   - Log out when done

---

## 📁 Admin Panel File Structure

```
admin/
├── login.php          # Admin login page
├── dashboard.php      # Main admin dashboard
├── add-product.php    # Add new products
├── edit-product.php   # Edit existing products
├── delete-product.php # Delete products
├── products.php       # Manage all products
├── view-orders.php    # View and manage orders
└── view-reviews.php   # Manage customer reviews
```

---

## 🆘 Troubleshooting

**Can't access admin panel?**
- Check if you're logged in with admin account
- Verify user role is set to 'admin' in database
- Clear browser cache/cookies

**Orders not showing?**
- Check database connection
- Verify orders table exists
- Check user permissions

**Can't upload images?**
- Check `assets/images/products/` folder permissions
- Verify file size (max 5MB)
- Check file format (JPEG, PNG, GIF, WebP only)

---

## 📞 Support

For issues or questions:
1. Check database connection in `includes/config.php`
2. Verify all SQL files are imported
3. Check file permissions
4. Review error messages in browser console

---

**Last Updated:** 2024
**Version:** 1.0

