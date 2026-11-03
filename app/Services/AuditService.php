<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        string $description,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null
    ): AuditLog {
        /** @var User|null $user */
        $user = $actor ?? Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'actor_name' => $user?->name ?? 'System / Guest',
            'actor_role' => $user?->role ?? 'system',
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip() ?? '127.0.0.1',
        ]);
    }
}
