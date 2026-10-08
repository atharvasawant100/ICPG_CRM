<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    // Provides $this->authorize(), which routes through the Gate and therefore
    // respects the Gate::before hook that grants the admin role everything.
    use AuthorizesRequests, ValidatesRequests;
}