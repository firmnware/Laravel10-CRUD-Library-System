<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Member;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ========== BUAT ADMIN ==========
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@library.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '081234567890',
            'address' => 'Jl. Admin No. 1',
        ]);

        // ========== BUAT MEMBER ==========
        $member1 = User::create([
            'name' => 'John Doe',
            'email' => 'member@library.com',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'status' => 'active',
            'phone' => '081234567891',
            'address' => 'Jl. Member No. 123',
        ]);

        Member::create([
            'user_id' => $member1->id,
            'member_code' => 'MBR-00001',
            'join_date' => now(),
            'status' => 'active'
        ]);

        $member2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'status' => 'active',
            'phone' => '081234567892',
            'address' => 'Jl. Member No. 456',
        ]);

        Member::create([
            'user_id' => $member2->id,
            'member_code' => 'MBR-00002',
            'join_date' => now(),
            'status' => 'active'
        ]);

        // ========== BUAT 10 KATEGORI BUKU ==========
        $categories = [
            ['name' => 'Fiksi', 'description' => 'Buku cerita fiksi dan novel'],
            ['name' => 'Non Fiksi', 'description' => 'Buku pengetahuan dan informasi'],
            ['name' => 'Teknologi', 'description' => 'Buku tentang teknologi dan pemrograman'],
            ['name' => 'Sains', 'description' => 'Buku ilmu pengetahuan alam'],
            ['name' => 'Sejarah', 'description' => 'Buku tentang sejarah dan peradaban'],
            ['name' => 'Bisnis', 'description' => 'Buku tentang bisnis dan kewirausahaan'],
            ['name' => 'Psikologi', 'description' => 'Buku tentang psikologi dan pengembangan diri'],
            ['name' => 'Pendidikan', 'description' => 'Buku tentang pendidikan dan pembelajaran'],
            ['name' => 'Kesehatan', 'description' => 'Buku tentang kesehatan dan gaya hidup'],
            ['name' => 'Agama', 'description' => 'Buku tentang agama dan spiritualitas'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // ========== FUNGSI UNTUK CEK GAMBAR ==========
        function getCoverPath($filename)
        {
            // Cek di folder public/storage/covers/
            $path = 'covers/' . $filename;
            
            // Jika file tidak ada, return null
            if (!Storage::disk('public')->exists($path)) {
                return null;
            }
            
            return $path;
        }

        // ========== BUAT BUKU DENGAN GAMBAR ==========
        $books = [
            // ===== 1. Fiksi (5 buku) =====
            [
                'title' => 'Dunia Sophie',
                'author' => 'Jostein Gaarder',
                'publisher' => 'Mizan',
                'year' => 2019,
                'isbn' => '978-602-1234-001',
                'stock' => 5,
                'category_id' => 1,
                'cover' => getCoverPath('dunia_sophie.jpg'),
                'description' => 'Novel filsafat yang mengisahkan perjalanan seorang gadis bernama Sophie dalam memahami dunia dan filsafat.'
            ],
            [
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'year' => 2005,
                'isbn' => '978-602-1234-002',
                'stock' => 4,
                'category_id' => 1,
                'cover' => getCoverPath('laskar_pelangi.jpg'),
                'description' => 'Kisah inspiratif tentang perjuangan 10 anak miskin di Belitung untuk mendapatkan pendidikan.'
            ],
            [
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'publisher' => 'Hasta Mitra',
                'year' => 1980,
                'isbn' => '978-602-1234-003',
                'stock' => 3,
                'category_id' => 1,
                'cover' => getCoverPath('bumi_manusia.jpg'),
                'description' => 'Novel sejarah yang menceritakan perjuangan pribumi melawan penjajahan Belanda.'
            ],
            [
                'title' => 'Perahu Kertas',
                'author' => 'Dee Lestari',
                'publisher' => 'Bentang Pustaka',
                'year' => 2009,
                'isbn' => '978-602-1234-004',
                'stock' => 4,
                'category_id' => 1,
                'cover' => getCoverPath('perahu_kertas.jpg'),
                'description' => 'Kisah tentang mimpi, cinta, dan perjuangan dua anak muda dalam meraih impian.'
            ],
            [
                'title' => 'Sang Pemimpi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'year' => 2006,
                'isbn' => '978-602-1234-005',
                'stock' => 3,
                'category_id' => 1,
                'cover' => getCoverPath('sang_pemimpi.jpg'),
                'description' => 'Sekuel Laskar Pelangi yang mengisahkan perjuangan Ikal dan Arai di Belitung.'
            ],

            // ===== 2. Non Fiksi (5 buku) =====
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'publisher' => 'Gramedia',
                'year' => 2020,
                'isbn' => '978-602-1234-006',
                'stock' => 6,
                'category_id' => 2,
                'cover' => getCoverPath('atomic_habits.jpg'),
                'description' => 'Membangun kebiasaan kecil yang mengubah hidup secara fundamental.'
            ],
            [
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'publisher' => 'Harper',
                'year' => 2014,
                'isbn' => '978-602-1234-007',
                'stock' => 4,
                'category_id' => 2,
                'cover' => getCoverPath('sapiens.jpg'),
                'description' => 'Sejarah singkat umat manusia dari zaman purba hingga era modern.'
            ],
            [
                'title' => 'The Power of Habit',
                'author' => 'Charles Duhigg',
                'publisher' => 'Random House',
                'year' => 2012,
                'isbn' => '978-602-1234-008',
                'stock' => 3,
                'category_id' => 2,
                'cover' => getCoverPath('power_of_habit.jpg'),
                'description' => 'Mengapa kita melakukan apa yang kita lakukan dan bagaimana mengubah kebiasaan.'
            ],
            [
                'title' => 'The Art of War',
                'author' => 'Sun Tzu',
                'publisher' => 'Gramedia',
                'year' => 2005,
                'isbn' => '978-602-1234-009',
                'stock' => 5,
                'category_id' => 2,
                'cover' => getCoverPath('art_of_war.jpg'),
                'description' => 'Strategi perang kuno yang masih relevan untuk bisnis dan kehidupan modern.'
            ],
            [
                'title' => 'Rich Dad Poor Dad',
                'author' => 'Robert T. Kiyosaki',
                'publisher' => 'PT. Bhuana Ilmu Populer',
                'year' => 2017,
                'isbn' => '978-602-1234-010',
                'stock' => 4,
                'category_id' => 2,
                'cover' => getCoverPath('rich_dad_poor_dad.jpg'),
                'description' => 'Pelajaran tentang finansial dan kebebasan ekonomi dari dua sosok ayah.'
            ],

            // ===== 3. Teknologi (4 buku) =====
            [
                'title' => 'Laravel 10 untuk Pemula',
                'author' => 'Sandhika Galih',
                'publisher' => 'Teknologi Press',
                'year' => 2024,
                'isbn' => '978-602-1234-011',
                'stock' => 5,
                'category_id' => 3,
                'cover' => getCoverPath('laravel_10.png'),
                'description' => 'Belajar Laravel dari dasar hingga mahir dengan studi kasus project nyata.'
            ],
            [
                'title' => 'Python Crash Course',
                'author' => 'Eric Matthes',
                'publisher' => 'No Starch Press',
                'year' => 2023,
                'isbn' => '978-602-1234-012',
                'stock' => 6,
                'category_id' => 3,
                'cover' => getCoverPath('python_crash.png'),
                'description' => 'Belajar Python dengan cepat dan praktis untuk pemula hingga mahir.'
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'year' => 2008,
                'isbn' => '978-602-1234-013',
                'stock' => 3,
                'category_id' => 3,
                'cover' => getCoverPath('clean_code.jpg'),
                'description' => 'Panduan menulis kode yang bersih, terstruktur, dan mudah dipelihara.'
            ],
            [
                'title' => 'JavaScript: The Good Parts',
                'author' => 'Douglas Crockford',
                'publisher' => 'O\'Reilly Media',
                'year' => 2008,
                'isbn' => '978-602-1234-014',
                'stock' => 4,
                'category_id' => 3,
                'cover' => getCoverPath('javascript_good_parts.jpg'),
                'description' => 'Fokus pada fitur-fitur terbaik JavaScript untuk pengembangan web modern.'
            ],

            // ===== 4. Sains (3 buku) =====
            [
                'title' => 'A Brief History of Time',
                'author' => 'Stephen Hawking',
                'publisher' => 'Bantam Books',
                'year' => 1988,
                'isbn' => '978-602-1234-015',
                'stock' => 3,
                'category_id' => 4,
                'cover' => getCoverPath('brief_history_time.jpg'),
                'description' => 'Penjelasan tentang alam semesta, lubang hitam, dan teori relativitas.'
            ],
            [
                'title' => 'The Selfish Gene',
                'author' => 'Richard Dawkins',
                'publisher' => 'Oxford University Press',
                'year' => 1976,
                'isbn' => '978-602-1234-016',
                'stock' => 4,
                'category_id' => 4,
                'cover' => getCoverPath('selfish_gene.jpg'),
                'description' => 'Teori evolusi dari perspektif gen yang egois.'
            ],
            [
                'title' => 'Cosmos',
                'author' => 'Carl Sagan',
                'publisher' => 'Random House',
                'year' => 1980,
                'isbn' => '978-602-1234-017',
                'stock' => 3,
                'category_id' => 4,
                'cover' => getCoverPath('cosmos.jpg'),
                'description' => 'Perjalanan menjelajahi alam semesta dan tempat manusia di dalamnya.'
            ],

            // ===== 5. Sejarah (3 buku) =====
            [
                'title' => 'Sejarah Dunia yang Disembunyikan',
                'author' => 'John Doe',
                'publisher' => 'Pustaka Utama',
                'year' => 2021,
                'isbn' => '978-602-1234-018',
                'stock' => 5,
                'category_id' => 5,
                'cover' => getCoverPath('sejarah_dunia.jpg'),
                'description' => 'Fakta-fakta menarik tentang sejarah dunia yang jarang diketahui.'
            ],
            [
                'title' => 'Indonesia: Sejarah Awal',
                'author' => 'M.C. Ricklefs',
                'publisher' => 'Gramedia',
                'year' => 2008,
                'isbn' => '978-602-1234-019',
                'stock' => 3,
                'category_id' => 5,
                'cover' => getCoverPath('indonesia_sejarah.jpg'),
                'description' => 'Sejarah Indonesia dari masa pra-sejarah hingga kemerdekaan.'
            ],
            [
                'title' => 'The Silk Roads',
                'author' => 'Peter Frankopan',
                'publisher' => 'Bloomsbury',
                'year' => 2015,
                'isbn' => '978-602-1234-020',
                'stock' => 4,
                'category_id' => 5,
                'cover' => getCoverPath('silk_roads.jpg'),
                'description' => 'Sejarah dunia melalui jalur sutra dan peradaban timur.'
            ],

            // ===== 6. Bisnis (3 buku) =====
            [
                'title' => 'The Lean Startup',
                'author' => 'Eric Ries',
                'publisher' => 'Crown Business',
                'year' => 2011,
                'isbn' => '978-602-1234-021',
                'stock' => 4,
                'category_id' => 6,
                'cover' => getCoverPath('lean_startup.jpg'),
                'description' => 'Metodologi untuk memulai bisnis dengan efisien dan mengurangi risiko.'
            ],
            [
                'title' => 'Zero to One',
                'author' => 'Peter Thiel',
                'publisher' => 'Crown Business',
                'year' => 2014,
                'isbn' => '978-602-1234-022',
                'stock' => 3,
                'category_id' => 6,
                'cover' => getCoverPath('zero_to_one.jpg'),
                'description' => 'Catatan tentang startup dan bagaimana menciptakan masa depan.'
            ],
            [
                'title' => 'Good to Great',
                'author' => 'Jim Collins',
                'publisher' => 'HarperBusiness',
                'year' => 2001,
                'isbn' => '978-602-1234-023',
                'stock' => 3,
                'category_id' => 6,
                'cover' => getCoverPath('good_to_great.jpg'),
                'description' => 'Mengapa beberapa perusahaan menjadi hebat dan yang lainnya tidak.'
            ],

            // ===== 7. Psikologi (3 buku) =====
            [
                'title' => 'Thinking, Fast and Slow',
                'author' => 'Daniel Kahneman',
                'publisher' => 'Farrar, Straus and Giroux',
                'year' => 2011,
                'isbn' => '978-602-1234-024',
                'stock' => 4,
                'category_id' => 7,
                'cover' => getCoverPath('thinking_fast_slow.jpg'),
                'description' => 'Dua sistem pemikiran manusia dan bagaimana mereka mempengaruhi keputusan.'
            ],
            [
                'title' => 'Mindset',
                'author' => 'Carol S. Dweck',
                'publisher' => 'Random House',
                'year' => 2006,
                'isbn' => '978-602-1234-025',
                'stock' => 3,
                'category_id' => 7,
                'cover' => getCoverPath('mindset.jpg'),
                'description' => 'Psikologi kesuksesan dan perbedaan antara mindset tetap dan berkembang.'
            ],
            [
                'title' => 'The Psychology of Money',
                'author' => 'Morgan Housel',
                'publisher' => 'Harriman House',
                'year' => 2020,
                'isbn' => '978-602-1234-026',
                'stock' => 5,
                'category_id' => 7,
                'cover' => getCoverPath('psychology_money.jpg'),
                'description' => 'Hubungan antara psikologi manusia dan keputusan keuangan.'
            ],

            // ===== 8. Pendidikan (3 buku) =====
            [
                'title' => 'Pendidikan Anak Usia Dini',
                'author' => 'Dr. Montessori',
                'publisher' => 'Pustaka Pendidikan',
                'year' => 2019,
                'isbn' => '978-602-1234-027',
                'stock' => 4,
                'category_id' => 8,
                'cover' => getCoverPath('pendidikan_anak.jpg'),
                'description' => 'Metode pendidikan untuk anak usia dini berbasis Montessori.'
            ],
            [
                'title' => 'The Teacher\'s Guide',
                'author' => 'John Hattie',
                'publisher' => 'Routledge',
                'year' => 2012,
                'isbn' => '978-602-1234-028',
                'stock' => 3,
                'category_id' => 8,
                'cover' => getCoverPath('teachers_guide.jpg'),
                'description' => 'Panduan praktis untuk guru dalam meningkatkan kualitas pembelajaran.'
            ],
            [
                'title' => 'Learning How to Learn',
                'author' => 'Barbara Oakley',
                'publisher' => 'TarcherPerigee',
                'year' => 2018,
                'isbn' => '978-602-1234-029',
                'stock' => 3,
                'category_id' => 8,
                'cover' => getCoverPath('learning_how_to_learn.jpg'),
                'description' => 'Strategi efektif untuk belajar dan menguasai materi dengan cepat.'
            ],

            // ===== 9. Kesehatan (3 buku) =====
            [
                'title' => 'Why We Sleep',
                'author' => 'Matthew Walker',
                'publisher' => 'Scribner',
                'year' => 2017,
                'isbn' => '978-602-1234-030',
                'stock' => 4,
                'category_id' => 9,
                'cover' => getCoverPath('why_we_sleep.jpg'),
                'description' => 'Eksplorasi tentang pentingnya tidur bagi kesehatan fisik dan mental.'
            ],
            [
                'title' => 'The Blue Zones',
                'author' => 'Dan Buettner',
                'publisher' => 'National Geographic',
                'year' => 2008,
                'isbn' => '978-602-1234-031',
                'stock' => 3,
                'category_id' => 9,
                'cover' => getCoverPath('blue_zones.jpg'),
                'description' => 'Rahasia kesehatan dan umur panjang dari 5 zona biru di dunia.'
            ],
            [
                'title' => 'Eat, Move, Sleep',
                'author' => 'Tom Rath',
                'publisher' => 'Missionday',
                'year' => 2013,
                'isbn' => '978-602-1234-032',
                'stock' => 3,
                'category_id' => 9,
                'cover' => getCoverPath('eat_move_sleep.jpg'),
                'description' => 'Panduan hidup sehat melalui pola makan, gerakan, dan tidur yang baik.'
            ],

            // ===== 10. Agama (3 buku) =====
            [
                'title' => 'The Power of Now',
                'author' => 'Eckhart Tolle',
                'publisher' => 'New World Library',
                'year' => 1997,
                'isbn' => '978-602-1234-033',
                'stock' => 4,
                'category_id' => 10,
                'cover' => getCoverPath('power_of_now.jpg'),
                'description' => 'Panduan spiritual untuk hidup di masa sekarang dan menemukan kedamaian.'
            ],
            [
                'title' => 'The Purpose Driven Life',
                'author' => 'Rick Warren',
                'publisher' => 'Zondervan',
                'year' => 2002,
                'isbn' => '978-602-1234-034',
                'stock' => 3,
                'category_id' => 10,
                'cover' => getCoverPath('purpose_driven_life.jpg'),
                'description' => 'Menemukan tujuan hidup melalui perspektif agama dan spiritualitas.'
            ],
            [
                'title' => 'Muhammad: A Prophet for Our Time',
                'author' => 'Karen Armstrong',
                'publisher' => 'HarperOne',
                'year' => 2006,
                'isbn' => '978-602-1234-035',
                'stock' => 3,
                'category_id' => 10,
                'cover' => getCoverPath('muhammad_prophet.jpg'),
                'description' => 'Biografi Nabi Muhammad SAW dan pesannya untuk dunia modern.'
            ],
        ];

        // ========== SIMPAN DATA BUKU ==========
        foreach ($books as $book) {
            $book['available_stock'] = $book['stock'];
            Book::create($book);
        }
    }
}