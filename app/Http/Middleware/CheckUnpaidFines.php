
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Loan;

class CheckUnpaidFines
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has any unpaid fines
        $hasUnpaidFines = Loan::where('user_id', $user->id)
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->exists();

        if ($hasUnpaidFines) {
            $totalFines = Loan::where('user_id', $user->id)
                ->where('fine_amount', '>', 0)
                ->where('fine_paid', false)
                ->sum('fine_amount');

            return redirect()->route('member.loans.index')
                ->with('error', "Anda memiliki denda yang belum dibayar sebesar Rp " . number_format($totalFines, 0, ',', '.') . ". Silakan bayar denda terlebih dahulu sebelum melakukan reservasi baru.");
        }

        return $next($request);
    }
}
