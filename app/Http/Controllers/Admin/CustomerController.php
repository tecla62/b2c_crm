<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct(private CustomerService $customerService) {}

    public function index(Request $request)
    {
        $query = Customer::with('assignedTo');

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%"));
        }
        if ($status = $request->status)  $query->where('status', $status);
        if ($type   = $request->type)    $query->where('type', $type);
        if ($source = $request->source)  $query->where('source', $source);

        $customers = $query->latest()->paginate(10)->withQueryString();
        $users     = User::with('role')->where('status', 'active')->orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'users'));
    }

    public function create()
    {
        $users = User::with('role')->where('status', 'active')->orderBy('name')->get();
        return view('admin.customers.create', compact('users'));
    }

    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'nullable|email|unique:customers,email',
            'mobile'       => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:150',
        ]);
        if (empty($data['email'])) unset($data['email']);
        if (empty($data['mobile'])) unset($data['mobile']);
        if (empty($data['company_name'])) unset($data['company_name']);

        $data['status'] = 'active';
        $data['type']   = 'individual';
        $customer = $this->customerService->create($data);
        return response()->json(['success' => true, 'customer' => $customer->only(['id','name','company_name'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'nullable|email|unique:customers',
            'mobile'       => 'nullable|string|max:20',
            'phone'        => 'nullable|string|max:20',
            'avatar'       => 'nullable|image|max:2048',
            'company_name' => 'nullable|string|max:150',
            'gst_number'   => 'nullable|string|max:20',
            'website'      => 'nullable|url|max:255',
            'address'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:10',
            'source'       => 'nullable|string|max:50',
            'type'         => 'required|in:individual,company',
            'status'       => 'required|in:active,inactive,blocked',
            'notes'        => 'nullable|string',
            'assigned_to'  => 'nullable|exists:users,id',
        ]);

        $customer = $this->customerService->create($data);

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer \"{$customer->name}\" created successfully.");
    }

    public function show(Customer $customer)
    {
        $customer->load('assignedTo', 'createdBy');
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $users = User::with('role')->where('status', 'active')->orderBy('name')->get();
        return view('admin.customers.edit', compact('customer', 'users'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => ['nullable', 'email', Rule::unique('customers')->ignore($customer->id)],
            'mobile'       => 'nullable|string|max:20',
            'phone'        => 'nullable|string|max:20',
            'avatar'       => 'nullable|image|max:2048',
            'company_name' => 'nullable|string|max:150',
            'gst_number'   => 'nullable|string|max:20',
            'website'      => 'nullable|url|max:255',
            'address'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:10',
            'source'       => 'nullable|string|max:50',
            'type'         => 'required|in:individual,company',
            'status'       => 'required|in:active,inactive,blocked',
            'notes'        => 'nullable|string',
            'assigned_to'  => 'nullable|exists:users,id',
        ]);

        $this->customerService->update($customer, $data);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);
        return response()->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }

    public function updateStatus(Request $request, Customer $customer)
    {
        $request->validate(['status' => 'required|in:active,inactive,blocked']);
        $this->customerService->toggleStatus($customer, $request->status);
        return response()->json(['success' => true, 'message' => 'Customer status updated.']);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action'          => 'required|in:delete,activate,deactivate,block',
            'customer_ids'    => 'required|array',
            'customer_ids.*'  => 'exists:customers,id',
        ]);

        $customers = Customer::whereIn('id', $request->customer_ids);

        match ($request->action) {
            'delete'     => $customers->get()->each(fn($c) => $this->customerService->delete($c)),
            'activate'   => $customers->update(['status' => 'active']),
            'deactivate' => $customers->update(['status' => 'inactive']),
            'block'      => $customers->update(['status' => 'blocked']),
        };

        return response()->json(['success' => true, 'message' => 'Bulk action completed successfully.']);
    }
}
