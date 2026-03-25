<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class LoggerService
{
    /**
     * Log an admin activity
     *
     * @param string $activityType Type of activity (e.g., 'create', 'update', 'delete', 'verify')
     * @param string $description Human readable description
     * @param Model|null $model The model being affected
     * @param array|null $oldData Original data before change
     * @param array|null $newData New data after change
     * @return AdminActivityLog|null
     */
    public static function log(
        string $activityType,
        string $description,
        ?Model $model = null,
        ?array $oldData = null,
        ?array $newData = null
    ) {
        // Only log if user is logged in
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();
        
        // Try to find the owner_id
        // If current user is owner, use their ID
        // If current user is admin, we need to find who owns the system/resource
        // For simplicity in this project context where Admin manages Owner's property:
        // We might need to adjust this logic if it's a multi-tenant system with relation
        
        $ownerId = null;
        
        if ($user->role === 'owner') {
            $ownerId = $user->id;
        } elseif ($user->role === 'admin') {
            // Check if user has an owner relation, or if the model has an owner
            // For now, let's assume specific business logic or null
            // In the AdminManagementController, it used auth()->id() as owner when owner logged in.
            // When Admin logs in, we need to capture who they are working for.
            
            // Attempt to get owner from the model if possible
            if ($model && method_exists($model, 'owner')) {
                $ownerId = $model->owner_id;
            } elseif ($model && isset($model->owner_id)) {
                $ownerId = $model->owner_id;
            }
             
            // Fallback: Get from Admin Profile
            if (!$ownerId) {
                $ownerId = $user->adminProfile?->owner_id ?? null;
            }
            
            // CRITICAL: If owner_id is still null (e.g. data inconsistency, missing profile), 
            // we CANNOT create the log entry because the DB requires owner_id.
            // Improve: Log this anomaly to system logs and skip the DB insert to prevent 500 error.
            if (!$ownerId) {
                \Illuminate\Support\Facades\Log::warning("Skipping Admin Audit Log: Unable to determine Owner ID for Admin ID {$user->id}. AdminProfile might be missing.");
                return null;
            }
        }

        // Calculate changes if not provided but data is

        // Calculate changes if not provided but data is
        $changes = null;
        if ($oldData && $newData) {
            $changes = array_diff_assoc($newData, $oldData);
        }

        return AdminActivityLog::create([
            'admin_id' => $user->id,
            'owner_id' => $ownerId, // Can be null if generic system action
            'activity_type' => $activityType,
            'activity_label' => ucfirst(str_replace('_', ' ', $activityType)),
            'model_name' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_data' => $oldData,
            'new_data' => $newData,
            'changes' => $changes,
            'notes' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
