# UML — Sistem Perpustakaan (trial_perpustakaan2)

Sumber kebenaran: `app/Models/*.php`, `database/migrations/*_create_*.php`,
`routes/web.php`, `app/Http/Controllers/*.php`.

## Class Diagram
- `class-diagram.puml` (sumber PlantUML — edit file ini bila model berubah)
- `class-diagram.png` / `class-diagram.svg` (hasil render)

## Sequence Diagram
| File | Alur | Sumber |
|---|---|---|
| `sequence-peminjaman` | Admin mencatat peminjaman (validasi → cek member → cek denda → lock stok → create) | `TransactionController@store`, `Member::canBorrow/hasUnpaidPenalties`, `Book::decreaseStock` |
| `sequence-pengembalian` | Admin memproses pengembalian (tepat waktu / terlambat → denda) | `TransactionController@returnBook` |
| `sequence-bayar-denda` | Admin mengonfirmasi + mencatat pembayaran denda | `PenaltyController@showPayForm/payPenalty` |
| `sequence-tambah-member` | Admin menambah member (User + Member + kode otomatis) | `MemberController@store`, `Member::booted` |

Masing-masing tersedia dalam `.puml` (sumber), `.png`, dan `.svg`.

## Render ulang
```bash
cd docs/uml
java -jar /tmp/opencode/plantuml.jar -tpng *.puml
java -jar /tmp/opencode/plantuml.jar -tsvg *.puml
```
