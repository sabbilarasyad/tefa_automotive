<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('CREATE FUNCTION fn_documentation_complete(p_pkb_id BIGINT UNSIGNED) RETURNS BOOLEAN DETERMINISTIC READS SQL DATA BEGIN DECLARE v_missing INT; SELECT COUNT(*) INTO v_missing FROM pkb_services ps WHERE ps.pkb_id = p_pkb_id AND ps.documentation_required = TRUE AND NOT EXISTS (SELECT 1 FROM documentations d WHERE d.pkb_service_id = ps.id); IF v_missing > 0 THEN RETURN FALSE; END IF; SELECT COUNT(*) INTO v_missing FROM pkb_spareparts psp WHERE psp.pkb_id = p_pkb_id AND psp.documentation_required = TRUE AND NOT EXISTS (SELECT 1 FROM documentations d WHERE d.pkb_sparepart_id = psp.id); RETURN v_missing = 0; END');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared('DROP FUNCTION IF EXISTS fn_documentation_complete');
        }
    }
};
