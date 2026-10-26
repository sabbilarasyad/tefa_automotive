<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pkb', function (Blueprint $table) {
            $table->string('mechanic_name', 150)->nullable()->after('mechanic_id');
        });
    }

    public function down(): void
    {
        Schema::table('pkb', function (Blueprint $table) {
            $table->dropColumn('mechanic_name');
        });
    }
};
