
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'action_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification has been read.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread.
     *
     * @return bool
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Mark notification as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return true;
        }

        return $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as unread.
     *
     * @return bool
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to filter by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted notification type.
     *
     * @return string
     */
    public function getFormattedType(): string
    {
        return match($this->type) {
            'reservation_created' => 'Reservasi Dibuat',
            'reservation_ready' => 'Reservasi Siap',
            'reservation_expiring' => 'Reservasi Akan Berakhir',
            'loan_due_reminder' => 'Pengingat Jatuh Tempo',
            'loan_overdue' => 'Keterlambatan',
            'fine_payment' => 'Pembayaran Denda',
            default => ucwords(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get icon class based on notification type.
     *
     * @return string
     */
    public function getIconClass(): string
    {
        return match($this->type) {
            'reservation_created' => 'fa-bookmark',
            'reservation_ready' => 'fa-check-circle',
            'reservation_expiring' => 'fa-clock',
            'loan_due_reminder' => 'fa-calendar-check',
            'loan_overdue' => 'fa-exclamation-triangle',
            'fine_payment' => 'fa-money-bill-wave',
            default => 'fa-bell',
        };
    }

    /**
     * Get badge color based on notification type.
     *
     * @return string
     */
    public function getBadgeColor(): string
    {
        return match($this->type) {
            'reservation_created' => 'blue',
            'reservation_ready' => 'green',
            'reservation_expiring' => 'orange',
            'loan_due_reminder' => 'yellow',
            'loan_overdue' => 'red',
            'fine_payment' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get time ago in human readable format.
     *
     * @return string
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }
}
