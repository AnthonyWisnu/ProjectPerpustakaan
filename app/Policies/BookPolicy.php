
<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Determine whether the user can view any books.
     *
     * @param User|null $user
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        // Anyone (including guests) can view books list
        return true;
    }

    /**
     * Determine whether the user can view the book.
     *
     * @param User|null $user
     * @param Book $book
     * @return bool
     */
    public function view(?User $user, Book $book): bool
    {
        // Anyone (including guests) can view single book
        return true;
    }

    /**
     * Determine whether the user can create books.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Only admins can create books
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the book.
     *
     * @param User $user
     * @param Book $book
     * @return bool
     */
    public function update(User $user, Book $book): bool
    {
        // Only admins can update books
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the book.
     *
     * @param User $user
     * @param Book $book
     * @return bool
     */
    public function delete(User $user, Book $book): bool
    {
        // Only admins can delete books
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the book.
     *
     * @param User $user
     * @param Book $book
     * @return bool
     */
    public function restore(User $user, Book $book): bool
    {
        // Only admins can restore books
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the book.
     *
     * @param User $user
     * @param Book $book
     * @return bool
     */
    public function forceDelete(User $user, Book $book): bool
    {
        // Only super admins can force delete books
        return $user->isSuperAdmin();
    }
}
