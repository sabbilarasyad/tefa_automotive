<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('CREATE FUNCTION fn_calculate_pkb_total(p_pkb_id BIGINT UNSIGNED) RETURNS DECIMAL(15,2) DETERMINISTIC READS SQL DATA BEGIN DECLARE v_total DECIMAL(15,2); SELECT COALESCE(SUM(subtotal), 0) INTO v_total FROM pkb_services WHERE pkb_id = p_pkb_id; SELECT v_total + COALESCE(SUM(subtotal), 0) INTO v_total FROM pkb_spareparts WHERE pkb_id = p_pkb_id; RETURN v_total; END');
            DB::unprepared('CREATE TRIGGER trg_pkb_status_history AFTER UPDATE ON pkb FOR EACH ROW BEGIN IF OLD.status <> NEW.status THEN INSERT INTO pkb_status_histories (pkb_id, from_status, to_status, changed_by, created_at) VALUES (NEW.id, OLD.status, NEW.status, @current_user_id, NOW()); END IF; END');
            DB::unprepared("CREATE TRIGGER trg_prevent_negative_stock BEFORE UPDATE ON spareparts FOR EACH ROW BEGIN IF NEW.stock_qty < 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok tidak boleh negatif'; END IF; END");
            DB::unprepared('CREATE TRIGGER trg_stock_movement_apply AFTER INSERT ON stock_movements FOR EACH ROW BEGIN UPDATE spareparts SET stock_qty = NEW.stock_after WHERE id = NEW.sparepart_id; END');

            return;
        }

        if ($driver === 'sqlite') {
            DB::unprepared('CREATE TRIGGER trg_pkb_status_history AFTER UPDATE OF status ON pkb WHEN OLD.status <> NEW.status BEGIN INSERT INTO pkb_status_histories (pkb_id, from_status, to_status, changed_by, created_at) VALUES (NEW.id, OLD.status, NEW.status, NULL, CURRENT_TIMESTAMP); END');
            DB::unprepared("CREATE TRIGGER trg_prevent_negative_stock BEFORE UPDATE OF stock_qty ON spareparts WHEN NEW.stock_qty < 0 BEGIN SELECT RAISE(ABORT, 'Stok tidak boleh negatif'); END");
            DB::unprepared('CREATE TRIGGER trg_stock_movement_apply AFTER INSERT ON stock_movements BEGIN UPDATE spareparts SET stock_qty = NEW.stock_after WHERE id = NEW.sparepart_id; END');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        foreach (['trg_pkb_status_history', 'trg_prevent_negative_stock', 'trg_stock_movement_apply'] as $trigger) {
            DB::unprepared('DROP TRIGGER IF EXISTS '.$trigger);
        }

        if ($driver === 'mysql') {
            DB::unprepared('DROP FUNCTION IF EXISTS fn_calculate_pkb_total');
        }
    }
};
