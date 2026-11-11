<?php

namespace App\Models;

use App\Services\NotificationCenter;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'module', 'description',
        'subject_type', 'subject_id', 'properties',
        'ip_address', 'user_agent',
    ];

    protected $casts = ['properties' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public static function log(string $action, string $module, string $description, $subject = null, array $properties = []): void
    {
        $actorId = auth()->id();

        static::create([
            'user_id'      => $actorId,
            'action'       => $action,
            'module'       => $module,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject ? $subject->id : null,
            'properties'   => $properties,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        NotificationCenter::activity($action, $module, $description, $subject, $actorId);
    }
}
