# Quick Start - Preview the UI

## Step-by-Step Guide to Preview Your Application

### Step 1: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Click **"Start"** button next to **Apache**
3. Click **"Start"** button next to **MySQL**
4. Both should show green "Running" status

### Step 2: Setup Database
1. Open your web browser
2. Go to: **http://localhost/phpmyadmin**
3. Click on **"Import"** tab (top menu)
4. Click **"Choose File"** button
5. Navigate to your project folder: `sales&inventory/database/schema.sql`
6. Select the file and click **"Go"** button
7. Wait for success message: "Import has been successfully finished"

### Step 3: Copy Project to XAMPP
1. Copy your entire project folder `sales&inventory`
2. Paste it into: **C:\xampp\htdocs\**
3. You can rename it to something simpler like `fastfood-pos` (optional)

### Step 4: Preview the UI
1. Open your web browser (Chrome, Firefox, Edge, etc.)
2. Type in the address bar:
   ```
   http://localhost/sales&inventory/
   ```
   OR if you renamed it:
   ```
   http://localhost/fastfood-pos/
   ```
3. Press Enter
4. You should see the **Fast Food POS** dashboard!

## What You Should See

✅ **Sidebar** on the left with navigation menu
✅ **Dashboard** with statistics cards showing:
   - Today's Orders
   - Today's Revenue
   - Low Stock Items
   - Total Products

✅ **Navigation menu** with:
   - Dashboard
   - Point of Sale
   - Products
   - Inventory
   - Sales History
   - Reports

## Test the UI

1. **Click "Point of Sale"** - You should see product cards
2. **Click "Products"** - You should see a table with sample products
3. **Click "Inventory"** - You should see stock levels
4. **Click "Sales History"** - You should see an empty table (no sales yet)

## Troubleshooting

### ❌ "This site can't be reached" or "Connection refused"
**Solution:** Make sure Apache is running in XAMPP Control Panel

### ❌ Blank page or errors
**Solution:** 
- Check if MySQL is running
- Verify database was imported correctly
- Open browser console (F12) to see errors

### ❌ "Database connection failed"
**Solution:**
- Check `config/database.php` - make sure password is correct (default is empty)
- Verify database `fastfood_inventory` exists in phpMyAdmin

### ❌ CSS/JavaScript not loading
**Solution:**
- Check file paths in browser console (F12)
- Make sure all files are in the correct folders

## Quick Test Without Database

If you just want to see the UI design without database:
1. The page will still load but show errors in console
2. You can see the layout, colors, and design
3. For full functionality, you need the database imported

---

**Need help?** Check the main README.md or INSTALLATION.md files for more details.

