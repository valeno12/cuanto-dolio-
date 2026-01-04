<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('is_locked');
        });

        // Set expiration for existing rooms (60 days from creation)
        DB::table('rooms')
            ->whereNull('expires_at')
            ->update(['expires_at' => DB::raw("created_at + INTERVAL '60 days'")]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
