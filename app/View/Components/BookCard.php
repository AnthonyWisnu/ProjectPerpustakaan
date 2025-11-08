
<?php

namespace App\View\Components;

use App\Models\Book;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

/**
 * BookCard Component
 *
 * Displays a book card with optional action buttons
 */
class BookCard extends Component
{
    /**
     * The book model
     *
     * @var Book
     */
    public Book $book;

    /**
     * Whether to show action buttons
     *
     * @var bool
     */
    public bool $showActions;

    /**
     * Create a new component instance.
     *
     * @param Book $book
     * @param bool $showActions
     * @return void
     */
    public function __construct(
        Book $book,
        bool $showActions = true
    ) {
        $this->book = $book;
        $this->showActions = $showActions;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.book-card');
    }
}
