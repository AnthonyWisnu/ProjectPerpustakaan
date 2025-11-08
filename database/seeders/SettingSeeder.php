
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Library Information
            ['key' => 'library_name', 'value' => 'Perpustakaan Digital', 'type' => 'string', 'group' => 'general', 'description' => 'Nama perpustakaan'],
            ['key' => 'library_logo', 'value' => null, 'type' => 'string', 'group' => 'general', 'description' => 'Logo perpustakaan'],
            ['key' => 'library_address', 'value' => 'Jl. Perpustakaan No. 1, Jakarta', 'type' => 'string', 'group' => 'general', 'description' => 'Alamat perpustakaan'],
            ['key' => 'library_phone', 'value' => '021-12345678', 'type' => 'string', 'group' => 'general', 'description' => 'Nomor telepon perpustakaan'],
            ['key' => 'library_email', 'value' => 'info@library.com', 'type' => 'string', 'group' => 'general', 'description' => 'Email perpustakaan'],
            ['key' => 'operating_hours', 'value' => 'Senin - Jumat: 08:00 - 17:00', 'type' => 'string', 'group' => 'general', 'description' => 'Jam operasional'],

            // Loan Configuration
            ['key' => 'loan_duration_days', 'value' => '7', 'type' => 'integer', 'group' => 'loan', 'description' => 'Durasi peminjaman default (hari)'],
            ['key' => 'max_extensions', 'value' => '1', 'type' => 'integer', 'group' => 'loan', 'description' => 'Maksimal perpanjangan'],
            ['key' => 'max_books_per_member', 'value' => '2', 'type' => 'integer', 'group' => 'loan', 'description' => 'Maksimal buku per anggota'],
            ['key' => 'max_active_reservations', 'value' => '3', 'type' => 'integer', 'group' => 'loan', 'description' => 'Maksimal reservasi aktif'],

            // Reservation Settings
            ['key' => 'reservation_expiry_hours', 'value' => '24', 'type' => 'integer', 'group' => 'reservation', 'description' => 'Waktu kadaluarsa reservasi (jam)'],
            ['key' => 'auto_cancel_expired', 'value' => '1', 'type' => 'boolean', 'group' => 'reservation', 'description' => 'Auto-cancel reservasi expired'],

            // Fine Settings
            ['key' => 'fine_rate_per_day', 'value' => '1000', 'type' => 'integer', 'group' => 'fine', 'description' => 'Denda per hari (Rupiah)'],
            ['key' => 'fine_grace_period_days', 'value' => '0', 'type' => 'integer', 'group' => 'fine', 'description' => 'Grace period denda (hari)'],
            ['key' => 'max_fine_amount', 'value' => '50000', 'type' => 'integer', 'group' => 'fine', 'description' => 'Maksimal jumlah denda (Rupiah)'],

            // Notification Settings
            ['key' => 'email_notifications_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Enable notifikasi email'],
            ['key' => 'reminder_h_minus_1', 'value' => '1', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Reminder H-1 jatuh tempo'],
            ['key' => 'reminder_h_minus_3', 'value' => '1', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Reminder H-3 jatuh tempo'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
