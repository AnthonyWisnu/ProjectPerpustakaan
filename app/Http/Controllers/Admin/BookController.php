<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request)
    {
        $query = Book::with('category');

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by availability
        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->where('available_stock', '>', 0);
            } elseif ($request->availability === 'out_of_stock') {
                $query->where('available_stock', '=', 0);
            }
        }

        $books = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.books.index', compact('books', 'categories'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.books.create', compact('categories'));
    }

    /**
     * Store a newly created book.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'publication_year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'language' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'total_stock' => ['required', 'integer', 'min:1'],
            'shelf_location' => ['nullable', 'string', 'max:50'],
        ]);

        // Set available stock equal to total stock for new books
        $validated['available_stock'] = $validated['total_stock'];

        // Generate barcode if not provided
        if (empty($validated['barcode'])) {
            $validated['barcode'] = 'BK-' . strtoupper(Str::random(10));
        }

        Book::create($validated);

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Book created successfully.');
    }

    /**
     * Display the specified book.
     */
    public function show($id)
    {
        $book = Book::with(['category', 'reservationItems.reservation.user', 'loans.user'])->findOrFail($id);
        return view('admin.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        return view('admin.books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified book.
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn,' . $id],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'publication_year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'language' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'total_stock' => ['required', 'integer', 'min:0'],
            'shelf_location' => ['nullable', 'string', 'max:50'],
        ]);

        // Adjust available stock based on change in total stock
        $stockDifference = $validated['total_stock'] - $book->total_stock;
        $validated['available_stock'] = max(0, $book->available_stock + $stockDifference);

        $book->update($validated);

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // Check if book has active loans or reservations
        if ($book->loans()->whereNull('returned_at')->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete book with active loans.');
        }

        if ($book->reservationItems()->whereHas('reservation', function ($query) {
            $query->whereIn('status', ['pending', 'ready']);
        })->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete book with active reservations.');
        }

        $book->delete();

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Book deleted successfully.');
    }

    /**
     * Restore soft-deleted book.
     */
    public function restore($id)
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Book restored successfully.');
    }
}
