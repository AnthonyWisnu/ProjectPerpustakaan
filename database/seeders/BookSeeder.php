
<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // Create 50 sample books with realistic data
        Book::factory()->count(50)->create()->each(function ($book) use ($categories) {
            // Assign random category to each book
            $book->category_id = $categories->random()->id;
            $book->save();
        });
    }
}
