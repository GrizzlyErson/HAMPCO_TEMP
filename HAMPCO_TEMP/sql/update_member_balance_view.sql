-- Update view for member balance summary to fix weight display for all member types
CREATE OR REPLACE VIEW member_balance_view AS
SELECT 
    pr.id,
    pr.member_id,
    CASE 
        WHEN pr.is_self_assigned = 1 THEN mst.product_name
        ELSE pl.product_name
    END as product_name,
    CASE 
        WHEN (pr.is_self_assigned = 1 AND mst.product_name IN ('Piña Seda', 'Pure Piña Cloth')) OR
             (pr.is_self_assigned = 0 AND pl.product_name IN ('Piña Seda', 'Pure Piña Cloth')) THEN '-'
        ELSE FORMAT(pr.weight_g, 3)
    END as weight_g,
    CASE 
        WHEN (pr.is_self_assigned = 1 AND mst.product_name IN ('Piña Seda', 'Pure Piña Cloth')) OR
             (pr.is_self_assigned = 0 AND pl.product_name IN ('Piña Seda', 'Pure Piña Cloth'))
        THEN CONCAT(pl.length_m, 'm x ', pl.width_m, 'in')
        ELSE '-'
    END AS measurements,
    CASE 
        WHEN (pr.is_self_assigned = 1 AND mst.product_name IN ('Piña Seda', 'Pure Piña Cloth')) OR
             (pr.is_self_assigned = 0 AND pl.product_name IN ('Piña Seda', 'Pure Piña Cloth'))
        THEN pl.quantity
        ELSE '-'
    END AS quantity,
    pr.unit_rate,
    pr.total_amount,
    pr.payment_status,
    pr.date_paid,
    pr.date_created,
    um.role as member_role
FROM payment_records pr
JOIN user_member um ON pr.member_id = um.id
LEFT JOIN member_self_tasks mst ON pr.production_id = mst.production_id AND pr.is_self_assigned = 1
LEFT JOIN production_line pl ON 
    CASE 
        WHEN pr.is_self_assigned = 0 THEN pl.prod_line_id = CAST(pr.production_id AS UNSIGNED)
        ELSE pl.prod_line_id = CAST(SUBSTRING(pr.production_id, 1, LOCATE('_', pr.production_id) - 1) AS UNSIGNED)
    END;