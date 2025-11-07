<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the activity log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that the activity log belongs to.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an activity.
     *
     * @param string $action
     * @param string|null $description
     * @param Model|null $model
     * @param User|null $user
     * @return self
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        ?User $user = null
    ): self {
        return self::create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
