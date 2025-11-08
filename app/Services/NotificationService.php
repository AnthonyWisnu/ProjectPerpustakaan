<?php

namespace App\Services;

use App\Jobs\SendDueDateReminderNotification;
use App\Jobs\SendFinePaymentNotification;
use App\Jobs\SendOverdueNotification;
use App\Jobs\SendReservationExpiringNotification;
use App\Jobs\SendReservationNotification;
use App\Jobs\SendReservationReadyNotification;
use App\Models\Loan;
use App\Models\Reservation;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send notification when a reservation is created.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function sendReservationCreated(Reservation $reservation): void
    {
        // Dispatch job to send notification
        SendReservationNotification::dispatch($reservation);

        // Create in-app notification
        $this->createInAppNotification(
            $reservation->user,
            'reservation_created',
            [
                'title' => 'Reservation Created',
                'message' => "Your reservation #{$reservation->reservation_code} has been created successfully.",
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'total_books' => $reservation->total_books,
                'expired_at' => $reservation->expired_at?->format('Y-m-d H:i:s'),
            ]
        );

        \Log::info("Reservation created notification sent", [
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
        ]);
    }

    /**
     * Send notification when a reservation is ready for pickup.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function sendReservationReady(Reservation $reservation): void
    {
        // Dispatch job to send notification
        SendReservationReadyNotification::dispatch($reservation);

        // Create in-app notification
        $this->createInAppNotification(
            $reservation->user,
            'reservation_ready',
            [
                'title' => 'Reservation Ready',
                'message' => "Your reservation #{$reservation->reservation_code} is ready for pickup!",
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'expired_at' => $reservation->expired_at?->format('Y-m-d H:i:s'),
            ]
        );

        \Log::info("Reservation ready notification sent", [
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
        ]);
    }

    /**
     * Send notification when a reservation is expiring soon.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function sendReservationExpiring(Reservation $reservation): void
    {
        // Dispatch job to send notification
        SendReservationExpiringNotification::dispatch($reservation);

        // Create in-app notification
        $this->createInAppNotification(
            $reservation->user,
            'reservation_expiring',
            [
                'title' => 'Reservation Expiring Soon',
                'message' => "Your reservation #{$reservation->reservation_code} will expire soon. Please pick up your books before {$reservation->expired_at?->format('Y-m-d H:i')}.",
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'expired_at' => $reservation->expired_at?->format('Y-m-d H:i:s'),
            ]
        );

        \Log::info("Reservation expiring notification sent", [
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
        ]);
    }

    /**
     * Send notification as a reminder for loan due date.
     *
     * @param Loan $loan
     * @return void
     */
    public function sendLoanDueReminder(Loan $loan): void
    {
        // Dispatch job to send notification
        SendDueDateReminderNotification::dispatch($loan);

        // Create in-app notification
        $this->createInAppNotification(
            $loan->user,
            'loan_due_reminder',
            [
                'title' => 'Loan Due Date Reminder',
                'message' => "Reminder: Your loan #{$loan->loan_code} for '{$loan->book->title}' is due on {$loan->due_date?->format('Y-m-d')}.",
                'loan_id' => $loan->id,
                'loan_code' => $loan->loan_code,
                'book_title' => $loan->book->title,
                'due_date' => $loan->due_date?->format('Y-m-d'),
            ]
        );

        \Log::info("Loan due reminder notification sent", [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
        ]);
    }

    /**
     * Send notification when a loan is overdue.
     *
     * @param Loan $loan
     * @return void
     */
    public function sendLoanOverdue(Loan $loan): void
    {
        // Dispatch job to send notification
        SendOverdueNotification::dispatch($loan);

        // Create in-app notification
        $this->createInAppNotification(
            $loan->user,
            'loan_overdue',
            [
                'title' => 'Loan Overdue',
                'message' => "Your loan #{$loan->loan_code} for '{$loan->book->title}' is overdue. Please return it as soon as possible to avoid additional fines.",
                'loan_id' => $loan->id,
                'loan_code' => $loan->loan_code,
                'book_title' => $loan->book->title,
                'due_date' => $loan->due_date?->format('Y-m-d'),
                'days_overdue' => $loan->getDaysOverdue(),
                'fine_amount' => $loan->fine_amount,
            ]
        );

        \Log::info("Loan overdue notification sent", [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'days_overdue' => $loan->getDaysOverdue(),
        ]);
    }

    /**
     * Send notification when a fine is paid.
     *
     * @param Loan $loan
     * @return void
     */
    public function sendFinePayment(Loan $loan): void
    {
        // Dispatch job to send notification
        SendFinePaymentNotification::dispatch($loan);

        // Create in-app notification
        $this->createInAppNotification(
            $loan->user,
            'fine_payment',
            [
                'title' => 'Fine Payment Received',
                'message' => "Your fine payment of Rp " . number_format($loan->fine_amount, 0, ',', '.') . " for loan #{$loan->loan_code} has been received.",
                'loan_id' => $loan->id,
                'loan_code' => $loan->loan_code,
                'fine_amount' => $loan->fine_amount,
                'paid_at' => $loan->fine_paid_at?->format('Y-m-d H:i:s'),
            ]
        );

        \Log::info("Fine payment notification sent", [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'fine_amount' => $loan->fine_amount,
        ]);
    }

    /**
     * Create an in-app notification.
     *
     * @param mixed $notifiable
     * @param string $type
     * @param array $data
     * @return void
     */
    protected function createInAppNotification($notifiable, string $type, array $data): void
    {
        try {
            $notifiable->notify(new \Illuminate\Notifications\DatabaseNotification([
                'type' => $type,
                'data' => $data,
            ]));
        } catch (\Exception $e) {
            \Log::error("Failed to create in-app notification", [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send bulk notifications to multiple users.
     *
     * @param array $users
     * @param string $type
     * @param array $data
     * @return void
     */
    public function sendBulkNotification(array $users, string $type, array $data): void
    {
        foreach ($users as $user) {
            $this->createInAppNotification($user, $type, $data);
        }

        \Log::info("Bulk notification sent", [
            'type' => $type,
            'user_count' => count($users),
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param string $notificationId
     * @param mixed $user
     * @return bool
     */
    public function markAsRead(string $notificationId, $user): bool
    {
        try {
            $notification = $user->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("Failed to mark notification as read", [
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param mixed $user
     * @return bool
     */
    public function markAllAsRead($user): bool
    {
        try {
            $user->unreadNotifications->markAsRead();
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to mark all notifications as read", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
