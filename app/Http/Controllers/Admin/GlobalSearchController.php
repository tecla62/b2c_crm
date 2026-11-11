<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetItem;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $results = collect();

        if ($user->hasPermission('customers.view')) {
            $results = $results->concat(
                Customer::where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('company_name', 'like', "%{$term}%")
                        ->orWhere('mobile', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                })->limit(5)->get()->map(fn($customer) => [
                    'type' => 'Customer',
                    'icon' => 'bi-person',
                    'title' => $customer->name,
                    'subtitle' => $customer->company_name ?: ($customer->mobile ?: $customer->email),
                    'url' => route('admin.customers.show', $customer),
                ])
            );
        }

        if ($user->hasPermission('quotations.view') || $user->hasPermission('estimations.view')) {
            $results = $results->concat(
                Quotation::with('customer')
                    ->where(function ($query) use ($term) {
                        $query->where('quotation_number', 'like', "%{$term}%")
                            ->orWhere('subject', 'like', "%{$term}%")
                            ->orWhere('status', 'like', "%{$term}%")
                            ->orWhereHas('customer', fn($customer) => $customer
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('company_name', 'like', "%{$term}%")
                                ->orWhere('mobile', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%"));
                    })->limit(5)->get()->map(fn($quotation) => [
                        'type' => 'Quotation',
                        'icon' => 'bi-file-earmark-text',
                        'title' => $quotation->quotation_number,
                        'subtitle' => trim(($quotation->customer?->name ?: 'No customer') . ($quotation->subject ? ' - ' . $quotation->subject : '')),
                        'url' => route('admin.quotations.show', $quotation),
                    ])
            );
        }

        if ($user->hasPermission('invoices.view')) {
            $results = $results->concat(
                Invoice::with('customer')
                    ->where(function ($query) use ($term) {
                        $query->where('invoice_number', 'like', "%{$term}%")
                            ->orWhere('subject', 'like', "%{$term}%")
                            ->orWhere('status', 'like', "%{$term}%")
                            ->orWhereHas('quotation', fn($quotation) => $quotation->where('quotation_number', 'like', "%{$term}%"))
                            ->orWhereHas('customer', fn($customer) => $customer
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('company_name', 'like', "%{$term}%")
                                ->orWhere('mobile', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%"));
                    })->limit(5)->get()->map(fn($invoice) => [
                        'type' => 'Invoice',
                        'icon' => 'bi-receipt',
                        'title' => $invoice->invoice_number,
                        'subtitle' => trim(($invoice->customer?->name ?: 'No customer') . ($invoice->subject ? ' - ' . $invoice->subject : '')),
                        'url' => route('admin.invoices.show', $invoice),
                    ])
            );
        }

        if ($user->hasPermission('transactions.view')) {
            $results = $results->concat(
                Transaction::with('customer')
                    ->where(function ($query) use ($term) {
                        $query->where('transaction_number', 'like', "%{$term}%")
                            ->orWhere('reference_no', 'like', "%{$term}%")
                            ->orWhere('payment_method', 'like', "%{$term}%")
                            ->orWhere('status', 'like', "%{$term}%")
                            ->orWhereHas('invoice', fn($invoice) => $invoice->where('invoice_number', 'like', "%{$term}%"))
                            ->orWhereHas('customer', fn($customer) => $customer
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('company_name', 'like', "%{$term}%")
                                ->orWhere('mobile', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%"));
                    })->limit(5)->get()->map(fn($transaction) => [
                        'type' => 'Payment',
                        'icon' => 'bi-cash-stack',
                        'title' => $transaction->transaction_number,
                        'subtitle' => $transaction->customer?->name ?: $transaction->reference_no,
                        'url' => route('admin.transactions.show', $transaction),
                    ])
            );
        }

        if ($user->hasPermission('expenses.view')) {
            $results = $results->concat(
                Expense::with('customer')
                    ->where(function ($query) use ($term) {
                        $query->where('expense_number', 'like', "%{$term}%")
                            ->orWhere('category', 'like', "%{$term}%")
                            ->orWhere('vendor_name', 'like', "%{$term}%")
                            ->orWhere('reference_no', 'like', "%{$term}%")
                            ->orWhere('payment_method', 'like', "%{$term}%")
                            ->orWhere('status', 'like', "%{$term}%")
                            ->orWhereHas('invoice', fn($invoice) => $invoice->where('invoice_number', 'like', "%{$term}%"))
                            ->orWhereHas('customer', fn($customer) => $customer
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('company_name', 'like', "%{$term}%")
                                ->orWhere('mobile', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%"));
                    })->limit(5)->get()->map(fn($expense) => [
                        'type' => 'Expense',
                        'icon' => 'bi-wallet2',
                        'title' => $expense->expense_number,
                        'subtitle' => $expense->vendor_name ?: $expense->customer?->name,
                        'url' => route('admin.expenses.show', $expense),
                    ])
            );
        }

        if ($user->hasPermission('assets.view')) {
            $results = $results->concat(
                AssetItem::where(function ($query) use ($term) {
                    $query->where('asset_code', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%");
                })->limit(5)->get()->map(fn($asset) => [
                    'type' => 'Asset',
                    'icon' => 'bi-box-seam',
                    'title' => $asset->asset_code . ' - ' . $asset->name,
                    'subtitle' => "Available: {$asset->available_quantity} {$asset->unit}",
                    'url' => route('admin.assets.index', ['search' => $asset->asset_code]),
                ])
            );
        }

        if ($user->hasPermission('services.view')) {
            $results = $results->concat(
                Service::where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->limit(5)->get()->map(fn($service) => [
                        'type' => 'Service',
                        'icon' => 'bi-list-check',
                        'title' => $service->title,
                        'subtitle' => $service->formatted_price,
                        'url' => route('admin.services.index', ['search' => $service->title]),
                    ])
            );
        }

        if ($user->hasPermission('users.view')) {
            $results = $results->concat(
                User::where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('mobile', 'like', "%{$term}%");
                })->limit(5)->get()->map(fn($matchedUser) => [
                    'type' => 'User',
                    'icon' => 'bi-person-gear',
                    'title' => $matchedUser->name,
                    'subtitle' => $matchedUser->email,
                    'url' => route('admin.users.show', $matchedUser),
                ])
            );
        }

        return response()->json(['results' => $results->take(20)->values()]);
    }
}
