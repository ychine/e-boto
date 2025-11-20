<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(public Request $request)
    {
    }

    public function log(
        string $action,
        string $title,
        ?string $description = null,
        ?Model $model = null,
        ?array $changes = null,
        ?Authenticatable $actor = null,
    ): void {
        $user = $actor ?? auth()->user();

        AuditLog::create([
            'action' => $action,
            'title' => $title,
            'description' => $description,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'changes' => $changes,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}
