<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::with(['creator', 'assignedPerson']);

        // Data scope. No-op for the admin, whose scope is "all".
        $query->applyDataScope($request->user(), 'customers');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('brands', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $customers = $query->latest()->paginate(15);
        $assignableUsers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('customers.index', compact('customers', 'assignableUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Customer::class);

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('customers.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $this->authorize('create', Customer::class);

        $data = $request->validated();
        $designatedName = trim((string) $request->input('designated_person'));
        
        $matchedUser = null;
        if ($request->filled('assigned_to')) {
            $matchedUser = User::find($request->input('assigned_to'));
        } elseif ($designatedName !== '') {
            $matchedUser = User::where('name', $designatedName)->orWhere('email', $designatedName)->first();
        }

        $designatedPersonId = $matchedUser ? $matchedUser->id : Auth::id();
        $designatedPersonName = $designatedName !== '' ? $designatedName : ($matchedUser ? $matchedUser->name : Auth::user()->name);

        $customer = Customer::create([
            ...$data,
            'designated_person' => $designatedPersonName,
            'assigned_to' => $designatedPersonId,
            'created_by' => $designatedPersonId,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $customer->load(['creator', 'assignedPerson']);

        $invoices = $customer->invoices()->latest()->take(6)->get();
        $totalInvoiced = (float) $customer->invoices()->sum('total_amount');
        $totalPaid = (float) $customer->invoices()->where('status', 'paid')->sum('total_amount');
        $totalOutstanding = (float) $customer->invoices()->whereIn('status', ['sent', 'partially_paid', 'overdue'])->sum('total_amount');
        $invoicesCount = $customer->invoices()->count();

        return view('customers.show', compact(
            'customer',
            'invoices',
            'totalInvoiced',
            'totalPaid',
            'totalOutstanding',
            'invoicesCount'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('customers.edit', compact('customer', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $data = $request->validated();
        $designatedName = trim((string) $request->input('designated_person'));

        $matchedUser = null;
        if ($request->filled('assigned_to')) {
            $matchedUser = User::find($request->input('assigned_to'));
        } elseif ($designatedName !== '') {
            $matchedUser = User::where('name', $designatedName)->orWhere('email', $designatedName)->first();
        }

        $designatedPersonId = $matchedUser ? $matchedUser->id : ($customer->assigned_to ?: $customer->created_by ?: Auth::id());
        $designatedPersonName = $designatedName !== '' ? $designatedName : ($matchedUser ? $matchedUser->name : ($customer->designated_person ?: Auth::user()->name));

        $customer->update([
            ...$data,
            'designated_person' => $designatedPersonName,
            'assigned_to' => $designatedPersonId,
            'created_by' => $designatedPersonId,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        try {
            $customer->delete();
        } catch (\Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
