# Kardinalitas Antar Tabel

Sistem Informasi Perpustakaan (trial_perpustakaan2)

Skema basis data: MariaDB / MySQL. PK tabel bisnis bernama `<tabel>_id`
(`users` tetap memakai PK `id` karena merupakan tabel framework Laravel).

---

## Ringkasan Relasi

| No | Relasi | Kardinalitas | Alasan Singkat |
|----|--------|:------------:|----------------|
| 1 | `categories` → `books` | **1:M** | Satu kategori memuat banyak buku |
| 2 | `users` (member) → `loans` | **1:M** | Satu member bisa meminjam banyak buku |
| 3 | `books` → `loans` | **1:M** | Satu judul buku bisa dipinjam berkali-kali |
| 4 | `users` (admin) → `loans` | **1:M** | Satu admin meminjamkan banyak peminjaman |
| 5 | `users` (member) → `penalties` | **1:M** | Satu member bisa terkena banyak denda |
| 6 | `loans` → `returns` | **1:1** | Satu peminjaman hanya dikembalikan sekali |
| 7 | `loans` → `penalties` | **1:1** | Satu peminjaman maksimal satu denda |
| 8 | `users` (admin) → `returns` | **1:M** | Satu admin memproses banyak pengembalian |
| 9 | `users` ↔ `books` | **M:M** | Dipecah via junction table `loans` |

---

## Pembahasan Tiap Relasi

### 1. `categories` **1:M** `books`

- Satu kategori memiliki banyak buku; satu buku hanya memiliki satu kategori.
- FK: `books.category_id` → `categories.categories_id`.
- Menghapus kategori tidak ikut menghapus buku: `ON DELETE SET NULL`
  (kolom nullable → bukunya menjadi tanpa kategori).
- `books` yang menyimpan FK, `categories` tidak — ini pola khas 1:M.

### 2 & 4. `users` **1:M** `loans` (dua peran)

- **Peran member** (relasi 2): FK `loans.member_id` → `users.id`,
  `ON DELETE CASCADE` — member dihapus, riwayat peminjamannya ikut hilang.
- **Peran admin pemroses** (relasi 4): FK `loans.processed_by` → `users.id`,
  nullable, `NULL ON DELETE` — sekadar jejak audit siapa yang meminjamkan.
- Boleh admin 1 meminjamkan dan admin 2 mengembalikan (shift/staf berbeda
  adalah kasus normal) — dua jejak disimpan di kolom terpisah.
- Tabel `users` yang sama dipakai dua kali karena peran aktif berbeda —
  wajar setelah merge `members` → `users`.

### 3. `books` **1:M** `loans`

- FK: `loans.book_id` → `books.books_id`, `ON DELETE CASCADE`.
- Satu buku bisa muncul di banyak baris peminjaman (riwayat), tapi satu
  peminjaman hanya merujuk satu buku.

### 5. `users` **1:M** `penalties`

- FK: `penalties.member_id` → `users.id`, `ON DELETE CASCADE`.
- Satu member boleh punya banyak riwayat denda (satu per kejadian telat).

### 6. `loans` **1:1** `returns`

- FK: `returns.loan_id` → `loans.loans_id`, **UNIQUE** (+ `ON DELETE CASCADE`).
- Constraint UNIQUE inilah yang memaksa kardinalitas 1:1 di level database:
  baris `returns` kedua untuk satu peminjaman yang sama akan ditolak.
- Sisi `loans` masih memungkinkan peminjaman tanpa pengembalian (0..1) —
  berlaku selama buku masih dipinjam, jadi bentuk penuhnya **1 : 0..1**.

### 7. `loans` **1:1** `penalties`

- FK: `penalties.loan_id` → `loans.loans_id`, `ON DELETE CASCADE`.
- Denda lahir ketika peminjaman dikembalikan melewati jatuh tempo.
- Satu peminjaman maksimal satu denda; bentuk penuhnya **1 : 0..1**
  (tidak semua peminjaman telat).

### 8. `users` **1:M** `returns`

- FK: `returns.processed_by` → `users.id`, nullable, `NULL ON DELETE`.
- Jejak audit siapa admin yang **menerima** pengembalian — boleh berbeda
  orang dengan admin yang **meminjamkan** (relasi 4).

### 9. `users` **M:M** `books` (via junction `loans`)

- Banyak user meminjam banyak buku — relasi ini tidak bisa ditampung
  FK di salah satu tabel saja.
- Solusi klasik: **tabel penghubung (junction table)** — `loans`,
  dengan FK `member_id` (ke `users`) dan `book_id` (ke `books`).
- Setiap baris `loans` merepresentasikan satu kejadian pinjam
  (pasangan member + buku), sehingga M:M terurai menjadi dua 1:M.

---

## Kode Kardinalitas

| Kode | Makna | Contoh di skema ini |
|------|-------|---------------------|
| 1:1 | Satu ke satu | `loans`–`returns`, `loans`–`penalties` (dipaksa oleh UNIQUE) |
| 1:M | Satu ke banyak | Semua relasi FK biasa (relasi 1–5, 8) |
| M:M | Banyak ke banyak | `users`–`books`, diurai via junction `loans` |

> Catatan sejarah (2026): tabel `members` lama telah di-merge ke `users`
> (relasi lama `User` 1:1 `Member` sudah tidak ada), dan `returns` adalah
> tabel baru hasil pemisahan dari `loans`.
