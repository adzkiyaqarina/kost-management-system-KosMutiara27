<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove position from users table (normalized to admin_profiles).
     * First migrates any existing data to admin_profiles.
     */
    public function up(): void
    {
        // Migrate existing position data from users to admin_profiles
        $owner = DB::table('user')->where('role', 'owner')->first();
        $ownerId = $owner?->id;

        $admins = DB::table('user')->where('role', 'admin')->whereNotNull('position')->get();
        foreach ($admins as $admin) {
            // Check if admin_profile already exists
            $existing = DB::table('admin_profiles')->where('user_id', $admin->id)->first();
            if ($existing) {
                // Update position if not set
                if (empty($existing->position)) {
                    DB::table('admin_profiles')->where('user_id', $admin->id)->update([
                        'position' => $admin->position,
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // Create new admin_profile
                DB::table('admin_profiles')->insert([
                    'user_id' => $admin->id,
                    'owner_id' => $ownerId,
                    'position' => $admin->position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }

    /**
     * Re-add position column and copy data back from admin_profiles.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 50)->nullable()->after('role');
        });

        // Copy position back from admin_profiles
        $profiles = DB::table('admin_profiles')->whereNotNull('position')->get();
        foreach ($profiles as $profile) {
            DB::table('user')->where('id', $profile->user_id)->update([
                'position' => $profile->position,
            ]);
        }
    }
};
