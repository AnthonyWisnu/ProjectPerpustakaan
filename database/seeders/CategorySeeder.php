
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fiksi',
                'slug' => 'fiksi',
                'description' => 'Buku cerita fiksi, novel, dan karya sastra',
            ],
            [
                'name' => 'Non-Fiksi',
                'slug' => 'non-fiksi',
                'description' => 'Buku pengetahuan umum, biografi, dan sejarah',
            ],
            [
                'name' => 'Referensi',
                'slug' => 'referensi',
                'description' => 'Buku referensi, ensiklopedia, dan kamus',
            ],
            [
                'name' => 'Majalah',
                'slug' => 'majalah',
                'description' => 'Majalah dan publikasi berkala',
            ],
            [
                'name' => 'Komik',
                'slug' => 'komik',
                'description' => 'Buku komik dan novel grafis',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
