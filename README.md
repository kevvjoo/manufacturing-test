# Manufacturing Dashboard — Deployment Guide

## 1. Menjalankan Aplikasi via Docker

Pastikan Docker & Docker Compose sudah terinstall.

\`\`\`bash
git clone <repo-url>
cd manufacturing-test
cp .env.example .env
docker compose up -d --build
\`\`\`

Tunggu hingga semua container (`app`, `nginx`, `db`) berjalan. Cek status:

\`\`\`bash
docker compose ps
\`\`\`

Generate application key (sekali saja, setelah container pertama kali up):

\`\`\`bash
docker compose exec app php artisan key:generate
\`\`\`

## 2. Konfigurasi Environment (.env)

Salin `.env.example` menjadi `.env` sebelum menjalankan `docker compose up`. Pastikan value berikut sesuai dengan konfigurasi Docker (bukan localhost):

| Variable | Value | Keterangan |
|---|---|---|
| `DB_HOST` | `db` | Nama service MariaDB di `docker-compose.yml`, bukan `127.0.0.1` |
| `DB_PORT` | `3306` | Port internal container (bukan port exposed `3307`) |
| `DB_DATABASE` | `manufacturing_test` | Harus sama dengan `MYSQL_DATABASE` di `docker-compose.yml` |
| `DB_USERNAME` | `root` | Harus sama dengan `MYSQL_USER` |
| `DB_PASSWORD` | `` | Harus sama dengan `MYSQL_PASSWORD` |
| `APP_URL` | `http://localhost:8080` | Sesuai port yang di-expose Nginx |

## 3. Import Database

Database akan otomatis ter-import saat container `db` dibuat **pertama kali**, melalui file `database/dataset.sql` yang di-mount ke `/docker-entrypoint-initdb.d/`.

Jika ingin import ulang manual (misal setelah container sudah pernah dibuat sebelumnya):

\`\`\`bash
docker compose exec -T db mysql -u root -prootsecret manufacturing_test < database/dataset.sql
\`\`\`

Atau reset total (hapus volume database, mulai bersih):

\`\`\`bash
docker compose down -v
docker compose up -d --build
\`\`\`

**Catatan:** `down -v` akan menghapus seluruh data di volume `db_data` — hanya gunakan jika ingin reset database ke kondisi awal dari `dataset.sql`.

## 4. URL Aplikasi & Endpoint API

Setelah container berjalan, aplikasi dapat diakses di:

- **Dashboard (Web):** `http://localhost:8080/`

**Endpoint API:**

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/dashboard` | Summary KPI, trend 7 hari, status breakdown, top 10 machine |
| GET | `/api/dashboard/machine/{machineCode}` | Detail performance satu mesin |
| GET | `/api/production-orders` | List work order (support search, filter status/date, sort, pagination) |
| GET | `/api/production-results` | List production result (filter actual_start terhadap waktu sekarang, status RUNNING) |

Contoh request dengan query parameter:

\`\`\`
GET /api/production-orders?search=SPC0002&status=RUNNING&date_from=2026-01-01&date_to=2026-06-30&sort_by=target_qty&sort_dir=asc&per_page=20&page=2
\`\`\`

## Menghentikan Aplikasi

\`\`\`bash
docker compose down
\`\`\`

Data database tetap tersimpan di volume `db_data` (tidak terhapus, kecuali gunakan `down -v`).