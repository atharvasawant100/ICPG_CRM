<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialAccountRequest;
use App\Http\Requests\UpdateSocialAccountRequest;
use App\Models\Brand;
use App\Models\SocialAccount;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    /**
     * Store a newly created social account under a brand.
     */
    public function store(StoreSocialAccountRequest $request)
    {
        $this->authorize('create', SocialAccount::class);

        $data = $request->validated();
        $brand = Brand::findOrFail($data['brand_id']);

        // Set client_id from the brand
        $data['customer_id'] = $brand->customer_id;
        $data['client_id'] = $brand->customer_id;
        $data['connection_status'] = $data['connection_status'] ?? 'connected';

        $account = SocialAccount::create($data);

        return redirect()->route('brands.show', $brand)
            ->with('success', "Social account '{$account->account_name}' ({$account->platform}) added successfully.");
    }

    /**
     * Update the specified social account.
     */
    public function update(UpdateSocialAccountRequest $request, SocialAccount $socialAccount)
    {
        $this->authorize('update', $socialAccount);

        $data = $request->validated();
        $socialAccount->update($data);

        return redirect()->route('brands.show', $socialAccount->brand_id)
            ->with('success', "Social account '{$socialAccount->account_name}' updated successfully.");
    }

    /**
     * Remove the specified social account.
     */
    public function destroy(SocialAccount $socialAccount)
    {
        $this->authorize('delete', $socialAccount);

        $brandId = $socialAccount->brand_id;
        $name = $socialAccount->account_name;
        $socialAccount->delete();

        return redirect()->route('brands.show', $brandId)
            ->with('success', "Social account '{$name}' disconnected/deleted successfully.");
    }
}
