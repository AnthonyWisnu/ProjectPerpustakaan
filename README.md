# 📚 Digital Library Management System

<p align="center">
  <img src="public/images/logo.png" alt="Digital Library System Logo" width="200">
</p>

<p align="center">
  <strong>Sistem Manajemen Perpustakaan Digital yang Modern dan Lengkap</strong>
</p>

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/Laravel-11.6.1-red.svg" alt="Laravel Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.2%2B-blue.svg" alt="PHP Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/Tests-222%20passing-brightgreen.svg" alt="Tests"></a>
  <a href="#"><img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License"></a>
</p>

---

## 📖 Tentang Project

Digital Library Management System adalah aplikasi web lengkap untuk mengelola perpustakaan modern dengan fitur-fitur canggih seperti reservasi online, sistem denda otomatis, QR code untuk pickup, dan notifikasi real-time. Dibangun dengan Laravel 11 dan Alpine.js untuk memberikan pengalaman pengguna yang responsif dan modern.

## ✨ Fitur Utama

### 🔐 Multi-Role Authentication
- **Member**: Pengguna perpustakaan dengan akses ke katalog dan reservasi
- **Admin**: Staff perpustakaan untuk manajemen operasional
- **Super Admin**: Akses penuh untuk konfigurasi sistem

### 📚 Manajemen Buku
- CRUD lengkap untuk buku dengan upload cover
- Kategorisasi buku yang fleksibel
- Sistem ISBN untuk identifikasi unik
- Barcode generation otomatis
- Stock management real-time
- Import/Export data buku (Excel)
- Search & filter canggih

### 🎫 Sistem Reservasi
- Reservasi online maksimal 3 buku
- QR Code untuk pickup verification
- Countdown timer untuk expiry (24 jam)
- Auto-expiry dengan background jobs
- Email/SMS notifications
- Status tracking (pending → ready → completed)

### 📖 Sistem Peminjaman
- Durasi pinjaman 7 hari (configurable)
- Perpanjangan otomatis (max 1x)
- Maksimal 5 buku aktif per member
- Due date reminders (3 hari & 1 hari sebelum)
- Loan history tracking

### 💰 Sistem Denda Otomatis
- Rp 1,000/hari keterlambatan
- Maximum fine cap: Rp 50,000
- Grace period support
- Partial payment (optional)
- Block reservations jika ada denda unpaid
- Payment history & receipts

### 🛒 Shopping Cart
- Add/remove buku dengan mudah
- Real-time stock checking
- Maximum 3 buku per reservation
- Persistent cart (database-based)

### 🔔 Notifikasi Multi-Channel
- **Email**: Reservation updates, due date reminders
- **Database**: In-app notifications
- **SMS**: (Optional) untuk reminder penting
- Event-driven dengan Laravel Queue

### 📊 Analytics & Reports
- Dashboard dengan statistik real-time
- Most borrowed books
- Member activity tracking
- Overdue loans monitoring
- Fine collection reports
- Export ke PDF/Excel

### 🎨 Frontend Modern
- **Alpine.js** components untuk interaktivitas
- Real-time cart management
- Live stock checker dengan auto-refresh
- Countdown timer dengan urgency indicators
- QR Scanner dengan camera access
- Responsive design (Tailwind CSS)

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 11.6.1
- **PHP**: 8.2+
- **Database**: MySQL 8.0+
- **Queue**: Redis / Database
- **Cache**: Redis (optional)

### Frontend
- **CSS Framework**: Tailwind CSS
- **JavaScript**: Alpine.js
- **Build Tool**: Vite
- **Icons**: Heroicons

### Testing
- **PHPUnit**: Feature & Unit Tests
- **Coverage**: 222 comprehensive tests
  - 31 Auth tests
  - 48 Member tests
  - 68 Admin tests
  - 75 Service tests

### Additional Libraries
- **SimpleSoftwareIO/simple-qrcode**: QR Code generation
- **Maatwebsite/Laravel-Excel**: Import/Export
- **Laravel Notifications**: Multi-channel notifications
- **Laravel Queue**: Background jobs

## 📋 Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x & NPM
- MySQL >= 8.0
- Redis (recommended untuk queue & cache)
- Web Server (Apache/Nginx)

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/AnthonyWisnu/ProjectPerpustakaan.git
cd ProjectPerpustakaan
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy .env file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=digital_library
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Library Configuration (Optional)

Konfigurasi default ada di `config/library.php`. Anda bisa override via `.env`:

```env
# Library Identity
LIBRARY_NAME="Perpustakaan Digital Kampus"
LIBRARY_CODE="PDK"

# Reservation Settings
MAX_ACTIVE_RESERVATIONS=3
MAX_BOOKS_PER_RESERVATION=3
RESERVATION_EXPIRY_HOURS=24

# Loan Settings
LOAN_DURATION_DAYS=7
MAX_ACTIVE_LOANS=5
LOAN_ALLOW_EXTENSION=true
LOAN_MAX_EXTENSIONS=1

# Fine Settings
FINE_RATE_PER_DAY=1000
FINE_MAX_AMOUNT=50000
FINE_BLOCK_RESERVATIONS=true

# Member Settings
MEMBER_NUMBER_PREFIX=MBR
MEMBER_NUMBER_LENGTH=6
```

### 6. Run Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

**Default Users:**
- **Super Admin**: `superadmin@library.test` / `password`
- **Admin**: `admin@library.test` / `password`
- **Member**: `member@library.test` / `password`

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 9. Start Server

```bash
# Development server
php artisan serve

# Start queue worker (new terminal)
php artisan queue:work

# Start scheduler (cron job untuk production)
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Aplikasi akan berjalan di `http://localhost:8000`

## 🎯 Cara Penggunaan

### Member (Pengguna Perpustakaan)

1. **Register Akun Baru**
   - Klik "Register" di halaman login
   - Isi form registrasi (nama, email, phone)
   - Member number akan di-generate otomatis (contoh: MBR000001)

2. **Browse & Search Buku**
   - Gunakan search bar untuk mencari buku
   - Filter berdasarkan kategori, author, atau tahun
   - Lihat detail buku termasuk stock availability

3. **Reservasi Buku**
   - Tambahkan buku ke cart (max 3 buku)
   - Review cart dan klik "Create Reservation"
   - QR Code akan di-generate untuk pickup

4. **Pickup Buku**
   - Datang ke perpustakaan dengan QR code
   - Staff akan scan QR untuk konfirmasi
   - Buku di-convert menjadi loan

5. **Perpanjangan Pinjaman**
   - Lihat active loans di dashboard
   - Klik "Extend" sebelum due date
   - Maximum 1x perpanjangan (7 hari tambahan)

### Admin (Staff Perpustakaan)

1. **Manajemen Buku**
   - CRUD buku dengan upload cover
   - Update stock secara manual
   - Import buku dari Excel
   - Generate barcode untuk buku

2. **Proses Reservasi**
   - Lihat pending reservations
   - Mark as "Ready" ketika buku tersedia
   - Scan QR code untuk pickup verification
   - Convert reservation → loan

3. **Manajemen Peminjaman**
   - Track active loans
   - Process book returns
   - Extend loans untuk member
   - Mark books as lost

4. **Proses Denda**
   - View overdue loans dengan denda
   - Process fine payments (cash/transfer)
   - Generate payment receipts
   - Track payment history

5. **Reports & Analytics**
   - View dashboard statistics
   - Export loan history
   - Monitor member activities
   - Generate custom reports

### Super Admin

- Semua fitur Admin +
- User management (create/edit/suspend users)
- System settings & configuration
- Activity log monitoring
- Database backup & maintenance

## 🧪 Testing

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/Auth/LoginTest.php
```

### Test Coverage

```bash
php artisan test --coverage
```

**Current Coverage:**
- ✅ 31 Authentication tests
- ✅ 48 Member functionality tests
- ✅ 68 Admin management tests
- ✅ 75 Service layer tests
- **Total: 222 tests**

## 📁 Struktur Project

```
ProjectPerpustakaan/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Admin controllers
│   │   ├── Auth/           # Authentication
│   │   └── Member/         # Member controllers
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic layer
│   │   ├── FineCalculator.php
│   │   ├── StockManager.php
│   │   └── QRCodeGenerator.php
│   ├── Jobs/               # Queue jobs
│   ├── Mail/               # Email templates
│   └── Notifications/      # Notification classes
├── config/
│   └── library.php         # Library-specific config
├── database/
│   ├── migrations/         # Database migrations
│   ├── factories/          # Model factories
│   └── seeders/            # Database seeders
├── resources/
│   ├── views/
│   │   ├── admin/          # Admin views
│   │   ├── auth/           # Auth views
│   │   └── member/         # Member views
│   └── js/
│       ├── app.js
│       └── components/     # Alpine.js components
├── tests/
│   ├── Feature/            # Feature tests
│   │   ├── Auth/
│   │   ├── Member/
│   │   └── Admin/
│   └── Unit/               # Unit tests
└── storage/
    └── app/public/
        ├── covers/         # Book covers
        ├── qrcodes/        # QR codes
        ├── barcodes/       # Barcodes
        └── profiles/       # Profile pictures
```

## ⚙️ Konfigurasi Background Jobs

### Scheduled Tasks (Cron Jobs)

Sistem menggunakan Laravel Scheduler untuk tasks otomatis:

```php
// app/Console/Kernel.php
$schedule->command('reservations:expire')->hourly();
$schedule->command('loans:send-reminders')->daily();
$schedule->command('fines:calculate')->daily();
```

**Setup Cron (Production):**

```bash
crontab -e
```

Tambahkan:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Workers

Untuk background processing (emails, notifications):

```bash
# Development
php artisan queue:work

# Production (dengan supervisor)
[program:library-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/worker.log
```

## 🔒 Security

- ✅ CSRF Protection di semua forms
- ✅ XSS Protection via Blade escaping
- ✅ SQL Injection Prevention via Eloquent ORM
- ✅ Password Hashing dengan Bcrypt
- ✅ Role-based Access Control (RBAC)
- ✅ Input Validation di semua endpoints
- ✅ Rate Limiting untuk login attempts

## 🐛 Troubleshooting

### Issue: Queue jobs tidak berjalan

```bash
# Pastikan queue worker running
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Issue: Email tidak terkirim

```bash
# Check .env configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

# Test email
php artisan tinker
Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

### Issue: Storage link error

```bash
# Re-create storage link
php artisan storage:link

# Check permissions
chmod -R 775 storage bootstrap/cache
```

## 🤝 Contributing

Kontribusi sangat welcome! Ikuti langkah berikut:

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

**Coding Standards:**
- Follow PSR-12 coding style
- Write tests untuk fitur baru
- Update documentation

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👥 Authors

- **Anthony Wisnu** - *Initial work* - [AnthonyWisnu](https://github.com/AnthonyWisnu)

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Alpine.js
- SimpleSoftwareIO/simple-qrcode
- Semua contributors yang telah membantu

## 📞 Support

Jika ada pertanyaan atau issue, silakan:
- Open an issue di [GitHub Issues](https://github.com/AnthonyWisnu/ProjectPerpustakaan/issues)
- Email: support@yourlibrary.com

---

<p align="center">Made with ❤️ for Digital Libraries</p>
