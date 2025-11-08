
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stock = fake()->numberBetween(1, 10);

        return [
            'isbn' => fake()->unique()->isbn13(),
            'title' => fake()->sentence(rand(2, 5)),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'publication_year' => fake()->year(),
            'synopsis' => fake()->paragraph(3),
            'total_stock' => $stock,
            'available_stock' => $stock,
            'shelf_location' => strtoupper(fake()->randomLetter()) . '-' . fake()->numberBetween(1, 50),
            'barcode' => 'BRC' . fake()->unique()->numberBetween(100000, 999999),
        ];
    }
}
