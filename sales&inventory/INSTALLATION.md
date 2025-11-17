# Installation Guide - Fast Food Sales & Inventory System

## Quick Start Guide

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp\` (default location)
3. Launch XAMPP Control Panel
4. Start **Apache** and **MySQL** services

### Step 2: Setup Database
1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on the **"Import"** tab
4. Click **"Choose File"** and select `database/schema.sql`
5. Click **"Go"** to import the database
6. You should see a success message

**Alternative Method (Using SQL Tab):**
1. Go to `http://localhost/phpmyadmin`
2. Click on **"SQL"** tab
3. Copy and paste the contents of `database/schema.sql`
4. Click **"Go"**

### Step 3: Copy Project Files
1. Copy the entire project folder to: `C:\xampp\htdocs\`
2. You can rename it to `fastfood-pos` or keep the original name
3. The full path should be: `C:\xampp\htdocs\fastfood-pos\`

### Step 4: Configure Database (if needed)
If your MySQL has a password or different settings:
1. Open `config/database.php`
2. Update these lines if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Add your password here if you have one
   define('DB_NAME', 'fastfood_inventory');
   ```

### Step 5: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/fastfood-pos/`
   (Replace `fastfood-pos` with your folder name)
3. You should see the dashboard!

## Verification Checklist

- [ ] Apache is running in XAMPP Control Panel
- [ ] MySQL is running in XAMPP Control Panel
- [ ] Database `fastfood_inventory` exists in phpMyAdmin
- [ ] Sample data (categories and products) are visible in database
- [ ] Application loads at `http://localhost/fastfood-pos/`
- [ ] Dashboard shows statistics
- [ ] Products page shows sample products

## Common Issues & Solutions

### Issue: "Connection failed" error
**Solution:**
- Check if MySQL is running in XAMPP
- Verify database credentials in `config/database.php`
- Make sure database `fastfood_inventory` exists

### Issue: "404 Not Found" or blank page
**Solution:**
- Verify files are in `C:\xampp\htdocs\` directory
- Check folder name matches URL
- Ensure Apache is running
- Check `error_log` in XAMPP for details

### Issue: Products not showing
**Solution:**
- Verify database was imported correctly
- Check if `products` table has data
- Open browser console (F12) and check for JavaScript errors
- Verify API endpoints are accessible

### Issue: Can't complete orders
**Solution:**
- Check browser console for errors
- Verify `api/sales.php` file exists
- Check database connection
- Ensure all required tables exist

## Testing the System

1. **Test Dashboard:**
   - Should show today's orders (0 initially)
   - Should show revenue
   - Should list top products

2. **Test POS:**
   - Click on products to add to cart
   - Adjust quantities
   - Complete an order
   - Check if stock decreases

3. **Test Products:**
   - Add a new product
   - Edit an existing product
   - Delete a product (be careful!)

4. **Test Inventory:**
   - View stock levels
   - Adjust stock quantities
   - Check low stock alerts

## Next Steps

After installation:
1. Customize products to match your menu
2. Add your own categories
3. Set up proper pricing
4. Train staff on using the POS system
5. Regular backups of the database

## Backup Database

To backup your database:
1. Go to `http://localhost/phpmyadmin`
2. Select `fastfood_inventory` database
3. Click **"Export"** tab
4. Choose **"Quick"** or **"Custom"** method
5. Click **"Go"** to download SQL file

## Support

If you encounter issues:
1. Check XAMPP error logs: `C:\xampp\apache\logs\error.log`
2. Check PHP error logs: `C:\xampp\php\logs\php_error_log`
3. Enable error display in PHP (for development only)
4. Check browser console (F12) for JavaScript errors

---

**Happy selling! 🍔**


