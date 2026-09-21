<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'email', 'tier']);

        return response()->json(['data' => $customers]);
    }
}
