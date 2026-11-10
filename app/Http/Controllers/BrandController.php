<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\SocialAccount;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Brand::class);

        $query = Brand::query()
            ->with(['client', 'socialAccounts'])
            ->withCount(['contentPosts', 'socialAccounts']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('industry', 'like', $search)
                  ->orWhere('description', 'like', $search)
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'like', $search)
                         ->orWhere('company_name', 'like', $search);
                  });
            });
        }

        $brands = $query->latest('id')->paginate(15)->withQueryString();
        $clients = Customer::orderBy('name')->get(['id', 'name', 'company_name']);

        return view('brands.index', compact('brands', 'clients'));
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Brand::class);

        $clients = Customer::orderBy('name')->get(['id', 'name', 'company_name']);
        $selectedClientId = $request->input('client_id');

        return view('brands.create', compact('clients', 'selectedClientId'));
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        $this->authorize('create', Brand::class);

        $data = $request->validated();
        $data['customer_id'] = $data['client_id'] ?? ($data['customer_id'] ?? null);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        return redirect()->route('brands.show', $brand)
            ->with('success', "Brand '{$brand->name}' created successfully.");
    }

    /**
     * Display the specified brand and its social accounts.
     */
    public function show(Brand $brand)
    {
        $this->authorize('view', $brand);

        $brand->load(['client', 'socialAccounts']);
        $recentPosts = $brand->contentPosts()
            ->with(['assignee', 'reviewer'])
            ->latest('scheduled_date')
            ->limit(10)
            ->get();

        $platforms = SocialAccount::PLATFORMS;

        return view('brands.show', compact('brand', 'recentPosts', 'platforms'));
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand)
    {
        $this->authorize('update', $brand);

        $clients = Customer::orderBy('name')->get(['id', 'name', 'company_name']);

        return view('brands.edit', compact('brand', 'clients'));
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $this->authorize('update', $brand);

        $data = $request->validated();
        $data['customer_id'] = $data['client_id'] ?? ($data['customer_id'] ?? null);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($data);

        return redirect()->route('brands.show', $brand)
            ->with('success', "Brand '{$brand->name}' updated successfully.");
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand)
    {
        $this->authorize('delete', $brand);

        $name = $brand->name;
        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', "Brand '{$name}' deleted successfully.");
    }
}
