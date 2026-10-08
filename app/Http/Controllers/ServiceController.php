<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $this->authorize('viewAny', Service::class);

        $query = Service::with(['creator']);

        // Data scope. No-op for the admin.
        $query->applyDataScope($request->user(), 'services');

        // Apply filters
        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->latest()->paginate(15);

        return view('services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $this->authorize('create', Service::class);

        return view('services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {

        $this->authorize('create', Service::class);

        $user = Auth::user();
        $data = $request->validated();
        $data['created_by'] = $user->id;
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        // Clear custom_interval_days if frequency is not custom
        if ($data['frequency'] !== 'custom') {
            $data['custom_interval_days'] = null;
        }

        Service::create($data);

        return redirect()->route('services.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {

        $this->authorize('view', $service);

        $service->load(['creator', 'invoiceItems.invoice']);

        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {

        $this->authorize('update', $service);

        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {

        $this->authorize('update', $service);

        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $service->is_active;

        // Clear custom_interval_days if frequency is not custom
        if ($data['frequency'] !== 'custom') {
            $data['custom_interval_days'] = null;
        }

        $service->update($data);

        return redirect()->route('services.show', $service)
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {

        $this->authorize('delete', $service);

        // Check if service is used in any invoice items
        if ($service->invoiceItems()->count() > 0) {
            return redirect()->route('services.index')
                ->with('error', 'Cannot delete service that is used in invoices. Please deactivate it instead.');
        }

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
