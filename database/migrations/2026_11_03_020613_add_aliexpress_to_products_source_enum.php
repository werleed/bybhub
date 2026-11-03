<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE products MODIFY COLUMN source ENUM('1688','taobao','alibaba','aliexpress','manual','vendor') NULL");
    }

    public function down(): void
    {
        // Revert — note: existing 'aliexpress' rows will be set to NULL
        DB::statement("ALTER TABLE products MODIFY COLUMN source ENUM('1688','taobao','alibaba','manual','vendor') NULL");
    }
};
