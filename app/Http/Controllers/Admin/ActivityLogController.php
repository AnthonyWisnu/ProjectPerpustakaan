
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs with filtering and pagination.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by description
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($query) use ($request) {
                      $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        // Get unique actions for filter dropdown
        $actions = ActivityLog::distinct()->pluck('action')->sort();

        // Get users for filter dropdown
        $users = User::orderBy('name')->get();

        return view('admin.activity-logs.index', compact('logs', 'actions', 'users'));
    }

    /**
     * Display the specified activity log.
     */
    public function show($id)
    {
        $log = ActivityLog::with(['user', 'model'])->findOrFail($id);
        return view('admin.activity-logs.show', compact('log'));
    }

    /**
     * Remove the specified activity log from storage.
     */
    public function destroy($id)
    {
        $log = ActivityLog::findOrFail($id);
        $log->delete();

        return redirect()
            ->route('admin.activity-logs.index')
            ->with('success', 'Activity log deleted successfully.');
    }

    /**
     * Clear old activity logs.
     */
    public function clear(Request $request)
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $date = now()->subDays($validated['days']);
        $count = ActivityLog::where('created_at', '<', $date)->delete();

        return redirect()
            ->route('admin.activity-logs.index')
            ->with('success', "Successfully deleted {$count} activity logs older than {$validated['days']} days.");
    }
}
