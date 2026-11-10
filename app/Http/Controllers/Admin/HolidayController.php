<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    use AuthorizesByPermission;

    /**
     * Display a listing of holidays.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('holidays.view');

        $year = (int) ($request->input('year') ?: now()->year);
        $search = $request->string('search')->trim()->value();

        $query = Holiday::with('creator')->whereYear('date', $year);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allHolidays = $query->orderBy('date')->get();

        $today = now()->toDateString();
        $upcomingHolidays = $allHolidays->filter(fn ($h) => $h->date->toDateString() >= $today);
        $pastHolidays = $allHolidays->filter(fn ($h) => $h->date->toDateString() < $today);

        $years = Holiday::selectRaw('DISTINCT YEAR(date) as yr')
            ->pluck('yr')
            ->push(now()->year)
            ->push(now()->year + 1)
            ->unique()
            ->sortDesc()
            ->values();

        return view('admin.holidays.index', [
            'holidays' => $allHolidays,
            'upcomingHolidays' => $upcomingHolidays,
            'pastHolidays' => $pastHolidays,
            'year' => $year,
            'years' => $years,
            'search' => $search,
            'summary' => [
                'total' => $allHolidays->count(),
                'upcoming' => $upcomingHolidays->count(),
                'past' => $pastHolidays->count(),
            ],
        ]);
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('holidays.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date', 'unique:holidays,date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $holiday = Holiday::create([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $dateFormatted = Carbon::parse($holiday->date)->format('d M Y');

        return redirect()->route('admin.holidays.index', ['year' => Carbon::parse($holiday->date)->year])
            ->with('success', "Holiday '{$holiday->name}' for {$dateFormatted} has been scheduled.");
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $this->authorizePermission('holidays.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date', 'unique:holidays,date,' . $holiday->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $holiday->update($validated);

        return redirect()->route('admin.holidays.index', ['year' => Carbon::parse($holiday->date)->year])
            ->with('success', "Holiday '{$holiday->name}' updated successfully.");
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $this->authorizePermission('holidays.delete');

        $name = $holiday->name;
        $year = Carbon::parse($holiday->date)->year;
        $holiday->delete();

        return redirect()->route('admin.holidays.index', ['year' => $year])
            ->with('success', "Holiday '{$name}' has been deleted.");
    }
}
