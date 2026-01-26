# Database Schema - Notification Action System

## Required Tables and Columns

The notification action system uses the following existing database tables:

### 1. `user_member` Table
**Purpose:** Store member account information

**Required Columns:**
- `id` (INT, PRIMARY KEY) - Member ID
- `fullname` (VARCHAR) - Member name
- `member_fullname` (VARCHAR) - Full display name
- `role` (VARCHAR) - Member role (weaver, knotter, warper, etc.)
- `member_phone` (VARCHAR) - Contact number
- `member_verify` (INT) - Verification status:
  - `0` = Pending verification
  - `1` = Verified/Approved
  - `-1` = Rejected

**Relevant for Verify/Reject Actions**

---

### 2. `member_self_tasks` Table
**Purpose:** Store tasks created by members

**Required Columns:**
- `id` (INT, PRIMARY KEY) - Task ID
- `production_id` (INT, FOREIGN KEY) - Production line ID
- `product_name` (VARCHAR) - Product name
- `member_id` (INT, FOREIGN KEY) - Member who created task
- `status` (VARCHAR) - Task status:
  - `'pending'` = Awaiting admin approval
  - `'approved'` = Admin approved
  - `'rejected'` = Admin rejected
  - `'in_progress'` = Being worked on
  - `'completed'` = Finished
- `date_created` (DATETIME) - When task was created
- `weight_g` (DECIMAL) - Task weight in grams
- `quantity` (INT) - Quantity
- `length_m` (DECIMAL) - Length in meters (optional)
- `width_in` (DECIMAL) - Width in inches (optional)

**Relevant for Approve/Reject Member Task Actions**

---

### 3. `task_assignments` Table
**Purpose:** Store assigned tasks for members

**Required Columns:**
- `id` (INT, PRIMARY KEY) - Assignment ID
- `prod_line_id` (INT, FOREIGN KEY) - Production line ID
- `member_id` (INT, FOREIGN KEY) - Assigned member
- `role` (VARCHAR) - Required role
- `status` (VARCHAR) - Assignment status:
  - `'pending'` = Not yet started
  - `'in_progress'` = Currently working
  - `'submitted'` = Completed and submitted
  - `'approved'` = Admin approved
  - `'declined'` = Member declined
  - `'reassigned'` = Reassigned to another member
- `deadline` (DATETIME) - Task deadline
- `decline_reason` (TEXT) - Why member declined
- `decline_status` (VARCHAR) - Decline tracking:
  - `NULL` = Not declined
  - `'declined'` = Recently declined
  - `'cleared'` = Admin cleared/acknowledged
- `updated_at` (DATETIME) - Last update timestamp

**Relevant for Reassign and Clear Actions**

---

### 4. `production_line` Table
**Purpose:** Store production line information

**Required Columns:**
- `production_id` (INT, PRIMARY KEY) - Production ID
- `production_code` (VARCHAR) - Production code/reference
- `product_name` (VARCHAR) - Product name

**Relevant for Task Details Display**

---

## Database Schema Modifications (If Needed)

### Check Existing Columns

If the following columns don't exist, add them:

```sql
-- Check if decline_status column exists in task_assignments
ALTER TABLE task_assignments ADD COLUMN decline_status VARCHAR(50) NULL DEFAULT NULL;

-- Check if updated_at column exists in task_assignments
ALTER TABLE task_assignments ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Check if member_verify column exists in user_member
ALTER TABLE user_member ADD COLUMN member_verify INT DEFAULT 0;
```

### Verify Data Types

Ensure columns have correct types:

```sql
-- Verify text columns can hold reasons/explanations
ALTER TABLE task_assignments MODIFY COLUMN decline_reason TEXT;

-- Verify status columns are VARCHAR
ALTER TABLE user_member MODIFY COLUMN member_verify INT;
ALTER TABLE member_self_tasks MODIFY COLUMN status VARCHAR(50);
ALTER TABLE task_assignments MODIFY COLUMN status VARCHAR(50);
```

---

## Data Relationships

```
user_member
    ├─ 1:N ─> member_self_tasks (member_id)
    └─ 1:N ─> task_assignments (member_id)

production_line
    ├─ 1:N ─> member_self_tasks (production_id)
    └─ 1:N ─> task_assignments (prod_line_id)

task_assignments
    └─ contains: decline_reason, decline_status (for notifications)

member_self_tasks
    └─ contains: status (pending/approved/rejected)
```

---

## Action-Specific Data Updates

### Verify Member Action
**SQL:**
```sql
UPDATE user_member SET member_verify = 1 WHERE id = ?;
```
**Effect:** Member marked as verified, removes from pending notifications

---

### Reject Member Action
**SQL:**
```sql
UPDATE user_member SET member_verify = -1 WHERE id = ?;
```
**Effect:** Member marked as rejected, removes from pending notifications

---

### Approve Member Task Action
**SQL:**
```sql
UPDATE member_self_tasks SET status = 'approved' WHERE id = ?;
```
**Effect:** Task marked as approved, removes from pending tasks

---

### Reject Member Task Action
**SQL:**
```sql
UPDATE member_self_tasks SET status = 'rejected' WHERE id = ?;
```
**Effect:** Task marked as rejected, appears in rejected list

---

### Reassign Task Action
**SQL:**
```sql
UPDATE task_assignments SET 
    status = 'pending', 
    decline_reason = NULL, 
    decline_status = NULL,
    updated_at = NOW() 
WHERE id = ?;
```
**Effect:** Task reset to pending, becomes available for reassignment

---

### Clear Decline Notification Action
**SQL:**
```sql
UPDATE task_assignments SET decline_status = 'cleared' WHERE id = ?;
```
**Effect:** Marks notification as cleared, filters from future fetches

---

## Database Query Verification

### Check Current Status Counts

```sql
-- Unverified members count
SELECT COUNT(*) FROM user_member WHERE member_verify = 0;

-- Member created tasks (pending)
SELECT COUNT(*) FROM member_self_tasks WHERE status = 'pending';

-- Declined task assignments
SELECT COUNT(*) FROM task_assignments WHERE status = 'declined' AND decline_status IS NULL;

-- Cleared decline notifications
SELECT COUNT(*) FROM task_assignments WHERE decline_status = 'cleared';
```

### Sample Data Queries

```sql
-- Get pending member tasks
SELECT id, product_name, member_id, status, date_created 
FROM member_self_tasks 
WHERE status = 'pending' 
ORDER BY date_created DESC;

-- Get declined tasks
SELECT ta.id, pl.production_code, pl.product_name, um.fullname, ta.decline_reason
FROM task_assignments ta
JOIN production_line pl ON ta.prod_line_id = pl.production_id
JOIN user_member um ON ta.member_id = um.id
WHERE ta.status = 'declined' AND ta.decline_status IS NULL
ORDER BY ta.updated_at DESC;

-- Get pending member verification
SELECT id, member_fullname, role, member_phone 
FROM user_member 
WHERE member_verify = 0 
ORDER BY id DESC;
```

---

## Backup Before Changes

**Important:** Always backup your database before making schema changes:

```bash
# Backup command (adjust paths as needed)
mysqldump -u root -p hampco > hampco_backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## Index Optimization

For better query performance with notification fetches:

```sql
-- Index for member verification checks
CREATE INDEX idx_member_verify ON user_member(member_verify);

-- Index for member task status
CREATE INDEX idx_member_tasks_status ON member_self_tasks(status, date_created);

-- Index for task assignment status
CREATE INDEX idx_task_assign_status ON task_assignments(status, decline_status, updated_at);

-- Composite index for declining tasks
CREATE INDEX idx_task_decline ON task_assignments(status, decline_status, decline_reason);
```

---

## Migration Checklist

- [ ] Verify all required columns exist in tables
- [ ] Check data types match specifications
- [ ] Ensure foreign key relationships are intact
- [ ] Backup database before any changes
- [ ] Create suggested indexes for performance
- [ ] Test action endpoints with sample data
- [ ] Verify notifications display correctly after updates
- [ ] Monitor database performance after deployment

---

## Notes

- All table structures should already exist from project setup
- Column additions are optional but recommended for clarity
- Indexes significantly improve query performance for notifications
- Tests should verify both successful and failed state transitions
