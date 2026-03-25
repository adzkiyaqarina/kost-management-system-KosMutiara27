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
        // Get all rooms that have a legacy tenant assigned
        $rooms = DB::table('kamar')
            ->whereNotNull('current_tenant_id')
            ->get();

        foreach ($rooms as $room) {
            // Check if this tenant is already in occupants table to avoid duplicates
            $exists = DB::table('riwayat_penghuni_kamar')
                ->where('room_id', $room->id)
                ->where('user_id', $room->current_tenant_id)
                ->exists();

            if (!$exists) {
                DB::table('riwayat_penghuni_kamar')->insert([
                    'room_id' => $room->id,
                    'user_id' => $room->current_tenant_id,
                    'check_in_date' => $room->lease_start_date ?? now(),
                    'check_out_date' => $room->lease_end_date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Theoretically we could delete occupants that match current_tenant_id
        // but it's safer to keep them as data preservation.
        // This is mostly a one-way data repair migration.
    }
};
