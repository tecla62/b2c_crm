<?php

namespace App\Services;

use App\Mail\CustomerWelcomeMail;
use App\Mail\NewCustomerMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CustomerService
{
    public function create(array $data): Customer
    {
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $data['avatar'] = $data['avatar']->store('customers', 'public');
        }
        $data['created_by'] = auth()->id();

        $customer = Customer::create($data);
        ActivityLog::log('CREATE', 'Customers', "Created customer: {$customer->name}", $customer);

        // Notify company email
        $companyEmail = setting('company_email');
        if ($companyEmail) {
            try {
                Mail::to($companyEmail)->send(new NewCustomerMail($customer->load('assignedTo', 'createdBy')));
            } catch (\Throwable $e) {
                Log::error('NewCustomerMail failed: ' . $e->getMessage());
            }
        }

        // Send welcome email to customer
        if (!empty($customer->email)) {
            try {
                Mail::to($customer->email)->send(new CustomerWelcomeMail($customer));
            } catch (\Throwable $e) {
                Log::error('CustomerWelcomeMail failed: ' . $e->getMessage());
            }
        }

        return $customer;
    }

    public function update(Customer $customer, array $data): Customer
    {
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            if ($customer->avatar) Storage::disk('public')->delete($customer->avatar);
            $data['avatar'] = $data['avatar']->store('customers', 'public');
        }
        $customer->update($data);
        ActivityLog::log('UPDATE', 'Customers', "Updated customer: {$customer->name}", $customer);
        return $customer;
    }

    public function delete(Customer $customer): void
    {
        ActivityLog::log('DELETE', 'Customers', "Deleted customer: {$customer->name}", $customer);
        $customer->delete();
    }

    public function toggleStatus(Customer $customer, string $status): Customer
    {
        $customer->update(['status' => $status]);
        ActivityLog::log('STATUS_CHANGE', 'Customers', "Changed customer {$customer->name} status to {$status}", $customer);
        return $customer;
    }
}
