-- Create member_balance_summary view
CREATE OR REPLACE VIEW member_balance_summary AS
SELECT 
    pr.id,
    pr.member_id,
    pr.product_name,
    pr.weight_g,
    pr.measurements,
    pr.quantity,
    pr.unit_rate,
    pr.total_amount as total,
    pr.payment_status,
    pr.date_paid,
    pr.date_created,
    pr.date_updated,
    um.role as member_role
FROM payment_records pr
JOIN user_member um ON pr.member_id = um.id
WHERE pr.status = 'completed'
ORDER BY pr.date_created DESC;

-- Create indexes to improve query performance
CREATE INDEX idx_payment_records_member_id ON payment_records(member_id);
CREATE INDEX idx_payment_records_status ON payment_records(status);
CREATE INDEX idx_payment_records_payment_status ON payment_records(payment_status);
CREATE INDEX idx_payment_records_date_created ON payment_records(date_created);

-- Create a summary statistics view for quick access to totals
CREATE OR REPLACE VIEW member_earnings_summary AS
SELECT 
    member_id,
    COUNT(*) as total_tasks,
    SUM(CASE WHEN payment_status = 'Pending' THEN total_amount ELSE 0 END) as pending_payments,
    SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END) as completed_payments,
    SUM(total_amount) as total_earnings
FROM payment_records
WHERE status = 'completed'
GROUP BY member_id;