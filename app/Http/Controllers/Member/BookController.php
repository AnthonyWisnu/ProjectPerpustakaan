<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display book catalog with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Book::with('category')
            ->where('available_stock', '>', 0);

        // Search by title, author, or ISBN
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Sort options
        $sortBy = $request->get('sort', 'title');
        $sortOrder = $request->get('order', 'asc');

        $allowedSorts = ['title', 'author', 'publisher', 'publication_year', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $books = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        // Get user's cart items for comparison
        $cartBookIds = auth()->user()
            ->cartItems()
            ->pluck('book_id')
            ->toArray();

        return view('member.books.index', compact('books', 'categories', 'cartBookIds'));
    }

    /**
     * Display book details.
     */
    public function show($id)
    {
        $book = Book::with('category')->findOrFail($id);

        // Check if book is in user's cart
        $isInCart = $this->cartService->isInCart(auth()->user(), $book->id);

        return view('member.books.show', compact('book', 'isInCart'));
    }
}
