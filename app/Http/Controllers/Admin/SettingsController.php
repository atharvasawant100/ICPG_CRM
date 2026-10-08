<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

/**
 * Placeholder for future settings. Admin only, no behaviour yet.
 */
class SettingsController extends Controller
{
    use AuthorizesByPermission;
    public function index()
    {
        $this->authorizePermission('settings.view');

        return view('admin.settings.index');
    }
}
