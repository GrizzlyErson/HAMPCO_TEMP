# Task Approval Requests - Product Name Fix

## Problem
The Task Approval Requests table wasn't showing the product name for members with the weaver role, while other tables like "In Progress Tasks" showed the product name correctly.

## Root Cause
The `task_approval_requests` table had a restrictive ENUM constraint for the `product_name` column:
```sql
enum('Knotted Liniwan','Knotted Bastos','Warped Silk')
```

However, the `member_self_tasks` table supports 5 product types:
```sql
enum('Knotted Liniwan','Knotted Bastos','Warped Silk','Piña Seda','Pure Piña Cloth')
```

Weavers typically work with 'Piña Seda' and 'Pure Piña Cloth' products. When the database trigger `after_insert_self_task` tried to insert a weaver's task with these product names into `task_approval_requests`, MySQL would reject the INSERT operation because those values weren't in the ENUM constraint.

## Solution Implemented

### 1. Updated Database Schema (`hampco (1).sql`)
Modified the `task_approval_requests` table definition to include all product types:
```sql
`product_name` enum('Knotted Liniwan','Knotted Bastos','Warped Silk','Piña Seda','Pure Piña Cloth') NOT NULL
```

### 2. Added Database Migration (`admin/backend/db_updates.sql`)
Added migration script to update the existing `task_approval_requests` table:
```sql
ALTER TABLE task_approval_requests
MODIFY COLUMN product_name enum('Knotted Liniwan','Knotted Bastos','Warped Silk','Piña Seda','Pure Piña Cloth') NOT NULL;
```

### 3. Enhanced Backend Query (`admin/backend/end-points/get_task_requests.php`)
Made the query more robust with a fallback mechanism using COALESCE to get product_name from member_self_tasks if needed:
```php
SELECT 
    ...
    COALESCE(tar.product_name, mst.product_name) as product_name,
    COALESCE(tar.weight_g, mst.weight_g) as weight_g,
    COALESCE(tar.quantity, mst.quantity) as quantity,
    ...
FROM task_approval_requests tar
JOIN user_member um ON tar.member_id = um.id
LEFT JOIN member_self_tasks mst ON tar.production_id = mst.production_id AND tar.member_id = mst.member_id
WHERE tar.status = 'pending'
ORDER BY tar.date_created DESC
```

## How It Works Now
1. When a weaver creates a self-task with 'Piña Seda' or 'Pure Piña Cloth', the `after_insert_self_task` trigger successfully inserts it into `task_approval_requests`
2. The admin panel's Task Approval Requests table now correctly displays the product name for all roles including weavers
3. The query has built-in fallback to use member_self_tasks data if needed, ensuring data consistency

## Files Modified
- `hampco (1).sql` - Updated table definition
- `admin/backend/db_updates.sql` - Added migration script
- `admin/backend/end-points/get_task_requests.php` - Enhanced query with fallback

## Testing Steps
1. Run the migration: `ALTER TABLE task_approval_requests MODIFY COLUMN product_name enum('Knotted Liniwan','Knotted Bastos','Warped Silk','Piña Seda','Pure Piña Cloth') NOT NULL;`
2. Have a weaver create a new self-task with 'Piña Seda' product
3. Check the admin panel Task Approval Requests table
4. Verify that the product name now displays correctly for the weaver's task
