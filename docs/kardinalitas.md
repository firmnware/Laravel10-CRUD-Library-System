# Kardinalitas Antar Tabel

Sistem Informasi Perpustakaan (trial_perpustakaan2)

Skema basis data: MariaDB / MySQL. PK tabel bisnis bernama `<tabel>_id`
(hanya tabel `users` framework yang tetap memakai PK `id`).

Notasi kardinalitas: **1:1**, **1:M**, **M:M**.

---

## Ringkasan Relasi

| No | Relasi | Kardinalitas | Alasan Singkat |
|----|--------|:------------:|----------------|
| 1 | `categories` → `books` | **1:M** | Satu kategori memuat banyak buku |
| 2 | `users` → `loans` | **1:M** | Satu user bisa meminjam banyak buku |
| 3 | `books` → `loans` | **1:M** | Satu judul buku bisa dipinjam berkali-kali |
| 4 | `loans` → `returns` | **1:1** | Satu peminjaman hanya dikembalikan sekali |
| 5 | `returns` → `penalties` | **1:1** | Denda lahir dari SATU kejadian pengembalian telat |
| 6 | `users` → `penalties` | **1:M** | Satu user bisa terkena banyak denda |
| 7 | `users` ↔ `books` | **M:M** | Diurai via junction table `loans` |

> **Catatan peran:** `users` adalah satu tabel gabungan (admin + member,
> dibedakan kolom `role`) — TIDAK dipisah. Kolom audit `processed_by`
> di `loans` dan `returns` tetap menunjuk ke tabel `users` yang sama
> (nullable: admin yang meminjamkan / menerima pengembalian boleh
> berbeda orang), sehingga tidak menambah relasi baru.

---

## Pembahasan Tiap Relasi

### 1. `categories` **1:M** `books`

- Satu kategori memiliki banyak buku; satu buku hanya satu kategori.
- FK: `books.category_id` → `categories.categories_id`.
- Menghapus kategori tidak ikut menghapus buku: `ON DELETE SET NULL`.

### 2. `users` **1:M** `loans`

- FK: `loans.member_id` → `users.id`, `ON DELETE CASCADE`.
- Satu user boleh mempunyai banyak baris peminjaman (riwayat).
- Batas 3 peminjaman aktif ditegakkan di level aplikasi, bukan kardinalitas.

### 3. `books` **1:M** `loans`

- FK: `loans.book_id` → `books.books_id`, `ON DELETE CASCADE`.
- Satu buku muncul di banyak baris peminjaman; satu peminjaman hanya
  merujuk satu buku.

### 4. `loans` **1:1** `returns`

- FK: `returns.loan_id` → `loans.loans_id`, **UNIQUE** (+ `ON DELETE CASCADE`).
- Constraint UNIQUE memaksa kardinalitas 1:1 di level database: dua baris
  `returns` untuk satu peminjaman yang sama pasti ditolak.
- Bentuk penuhnya **1 : 0..1** — selama buku masih dipinjam, baris
  `returns`-nya belum ada.

### 5. `returns` **1:1** `penalties`

- Denda SELALU berasal dari keterlambatan, dan keterlambatan baru
  diketahui saat pengembalian terjadi — sumber relasinya tabel
  `returns`, bukan `loans`.
- FK: `penalties.return_id` → `returns.returns_id` (kolom FK UNIQUE).
- Satu pengembalian telat menghasilkan paling banyak satu denda;
  pengembalian tepat waktu tidak menghasilkan denda sama sekali
  (bentuk penuh **1 : 0..1**).
- `days_late` dan `fine_amount` diturunkan (derived) dari
  `returns.return_date` versus `loans.due_date` (via `returns.loan_id`).
- FK `penalties.member_id` → `users.id` tetap disimpan per kejadian,
  supaya daftar "denda milik siapa" tidak perlu join berlapis.

### 6. `users` **1:M** `penalties`

- FK: `penalties.member_id` → `users.id`, `ON DELETE CASCADE`.
- Satu user boleh punya banyak riwayat denda (satu per kejadian telat).

### 7. `users` **M:M** `books` (via junction `loans`)

- Banyak user meminjam banyak buku; relasi ini tidak bisa ditampung oleh
  satu FK saja.
- Solusi: **junction table** `loans` (FK `member_id` ke `users`, FK
  `book_id` ke `books`). Setiap baris `loans` = satu kejadian pinjam,
  sehingga M:M terurai menjadi dua 1:M.

---

## Kode Kardinalitas

| Kode | Makna | Contoh di skema ini |
|------|-------|---------------------|
| 1:1 | Satu ke satu | `loans`–`returns` (UNIQUE `loan_id`), `returns`–`penalties` (UNIQUE `return_id`) |
| 1:M | Satu ke banyak | Relasi 1–3, 6 |
| M:M | Banyak ke banyak | `users`–`books`, diurai via junction `loans` |

> Catatan sejarah (2026): tabel `members` lama telah di-merge ke `users`
> (relasi `User` 1:1 `Member` tidak ada lagi), dan `returns` adalah tabel
> baru hasil pemisahan dari `loans`. Tabel dulu bernama `transactions`
> kini bernama `loans`.
