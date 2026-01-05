-- Create view for member balance summary
CREATE OR REPLACE VIEW member_balance_view AS
SELECT 
    pr.id,
    pr.member_id,
    CASE 
        WHEN pr.is_self_assigned = 1 THEN mst.product_name
        ELSE pl.product_name
    END as product_name,
    pr.weight_g,
    CASE 
        WHEN pl.product_name IN ('Piña Seda', 'Pure Piña Cloth') 
        THEN CONCAT(pr.length_m, 'm x ', pr.width_m, 'm')
        ELSE '-'
    END AS measurements,
    pr.quantity,
    pr.unit_rate,
    pr.total_amount,
    pr.payment_status,
    pr.date_paid,
    pr.date_created,
    um.role as member_role
FROM payment_records pr
JOIN user_member um ON pr.member_id = um.id
LEFT JOIN production_line pl ON CASE 
    WHEN pr.is_self_assigned = 0 THEN CAST(pr.production_id AS UNSIGNED) = pl.prod_line_id
    ELSE FALSE
END
LEFT JOIN member_self_tasks mst ON CASE 
    WHEN pr.is_self_assigned = 1 THEN pr.production_id = mst.production_id
    ELSE FALSE
END
WHERE pr.payment_status IN ('Pending', 'Paid', 'Adjusted')
ORDER BY pr.date_created DESC;

-- Create view for member earnings summary
CREATE OR REPLACE VIEW member_earnings_summary AS
SELECT 
    member_id,
    COUNT(*) as total_tasks,
    SUM(CASE WHEN payment_status = 'Pending' THEN total_amount ELSE 0 END) as pending_payments,
    SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END) as completed_payments,
    SUM(total_amount) as total_earnings
FROM payment_records
GROUP BY member_id;