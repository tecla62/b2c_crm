<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer', 'createdBy', 'quotation');

        if ($s = $request->search) {
            $query->where(fn($q) => $q->where('invoice_number', 'like', "%$s%")
                ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%$s%"))
                ->orWhereHas('quotation', fn($q3) => $q3->where('quotation_number', 'like', "%$s%")));
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $invoices = $query->latest()->paginate(10)->withQueryString();
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $customers  = Customer::where('status', 'active')->orderBy('name')->get();
        $categories = ServiceCategory::with(['services' => fn($q) => $q->where('status', 'active')->orderBy('title')])
                        ->where('status', 'active')->orderBy('name')->get();
        $invoice    = null;

        $quotations = Quotation::with('customer', 'items')
                        ->latest()
                        ->get();

        $selectedQuotation = null;
        if ($qId = $request->query('quotation_id')) {
            $selectedQuotation = Quotation::with('customer', 'items')->find($qId);
        }

        $defaults   = [
            'guarantee' => $selectedQuotation?->guarantee ?? setting('invoice_guarantee', ''),
            'terms'     => $selectedQuotation?->terms     ?? setting('invoice_terms', ''),
            'payment'   => $selectedQuotation?->payment   ?? setting('invoice_payment', ''),
        ];
        return view('admin.invoices.form', compact('customers', 'categories', 'invoice', 'defaults', 'quotations', 'selectedQuotation'));
    }

    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);
        $data['invoice_number'] = Invoice::generateNumber();
        $data['created_by']     = auth()->id();

        $invoice = Invoice::create($data);
        $this->syncGroups($invoice, $request->input('groups', []));
        $this->recalcTotals($invoice, $data);

        ActivityLog::log('CREATE', 'Invoices', "Created invoice: {$invoice->invoice_number}", $invoice);
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items.service', 'items.category', 'quotation.items.service', 'quotation.items.category', 'createdBy');

        $accountSummary = $this->invoiceAccountSummary($invoice);

        $emailDefaults = [
            'subject' => $this->parseTemplate(
                setting('invoice_email_subject', 'Invoice {{invoice_number}} from {{company_name}}'),
                $invoice
            ),
            'body' => $this->parseTemplate(
                setting('invoice_email_body', "Dear {{customer_name}},\n\nPlease find your Invoice {{invoice_number}} attached.\n\nTotal Amount: {{total}}\n\nKindly process the payment as per the invoice terms.\n\nRegards,\n{{company_name}}"),
                $invoice
            ),
        ];

        return view('admin.invoices.show', compact('invoice', 'emailDefaults', 'accountSummary'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items', 'quotation.items');
        $customers  = Customer::where('status', 'active')->orderBy('name')->get();
        $categories = ServiceCategory::with(['services' => fn($q) => $q->where('status', 'active')->orderBy('title')])
                        ->where('status', 'active')->orderBy('name')->get();
        $quotations = Quotation::with('customer', 'items')->latest()->get();
        $selectedQuotation = $invoice->quotation;

        $defaults = [
            'guarantee' => $invoice->guarantee ?? '',
            'terms'     => $invoice->terms ?? '',
            'payment'   => $invoice->payment ?? '',
        ];
        return view('admin.invoices.form', compact('invoice', 'customers', 'categories', 'defaults', 'quotations', 'selectedQuotation'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateInvoice($request);
        $invoice->update($data);
        $this->syncGroups($invoice, $request->input('groups', []));
        $this->recalcTotals($invoice, $data);

        ActivityLog::log('UPDATE', 'Invoices', "Updated invoice: {$invoice->invoice_number}", $invoice);
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        ActivityLog::log('DELETE', 'Invoices', "Deleted invoice: {$invoice->invoice_number}", $invoice);
        $invoice->delete();
        return response()->json(['success' => true, 'message' => 'Invoice deleted.']);
    }

    public function print(Invoice $invoice)
    {
        $invoice->load('customer', 'items.service', 'items.category', 'createdBy', 'quotation');
        $accountSummary = $this->invoiceAccountSummary($invoice);

        return view('admin.invoices.print', compact('invoice', 'accountSummary'));
    }

    public function pdfDownload(Request $request, Invoice $invoice)
    {
        $invoice->load('customer', 'items.service', 'items.category', 'createdBy', 'quotation');
        $accountSummary = $this->invoiceAccountSummary($invoice);
        $showDesc = $request->boolean('desc', true);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.pdf', compact('invoice', 'accountSummary', 'showDesc'))
                  ->setPaper('a4', 'portrait');
        $filename = $invoice->invoice_number . '.pdf';
        return $pdf->download($filename);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status'    => 'required|in:draft,sent,paid,partial,overdue,cancelled',
            'show_desc' => 'nullable|in:0,1,true,false',
        ]);
        $oldStatus = $invoice->status;
        $newStatus = $request->status;

        $invoice->update(['status' => $newStatus]);
        ActivityLog::log('STATUS_CHANGE', 'Invoices', "Status → {$request->status}: {$invoice->invoice_number}", $invoice);

        $emailSent = false;

        if ($newStatus === 'sent') {
            try {
                $emailSent = $this->autoSendInvoiceEmail(
                    $invoice,
                    $request->boolean('show_desc', false)
                );
            } catch (\Throwable $e) {
                \Log::error('Auto-send invoice email failed: ' . $e->getMessage(), [
                    'invoice_id' => $invoice->id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated.' . ($emailSent ? ' Invoice emailed to customer ' . ($request->boolean('show_desc', false) ? 'with descriptions.' : 'without descriptions.') : ''),
            'badge'   => $invoice->fresh()->status_badge,
            'email_sent' => $emailSent,
        ]);
    }

    /**
     * Convert quotation to invoice — called from quotation module
     */
    public function convertFromQuotation(Request $request, Quotation $quotation)
    {
        // Prevent duplicate
        $existing = Invoice::where('quotation_id', $quotation->id)->first();
        if ($existing) {
            return response()->json([
                'success'  => true,
                'message'  => "Invoice {$existing->invoice_number} already exists.",
                'redirect' => route('admin.invoices.show', $existing),
            ]);
        }

        $invoice = $this->createInvoiceFromQuotation($quotation);
        ActivityLog::log('CREATE', 'Invoices', "Invoice {$invoice->invoice_number} created from quotation {$quotation->quotation_number}", $invoice);

        return response()->json([
            'success'  => true,
            'message'  => "Invoice {$invoice->invoice_number} created.",
            'redirect' => route('admin.invoices.show', $invoice),
        ]);
    }

    private function createInvoiceFromQuotation(Quotation $quotation): Invoice
    {
        $quotation->load('items');

        $invoice = Invoice::create([
            'invoice_number'   => Invoice::generateNumber(),
            'quotation_id'     => $quotation->id,
            'customer_id'      => $quotation->customer_id,
            'subject'          => $quotation->subject,
            'date'             => now()->toDateString(),
            'due_date'         => $quotation->valid_until,
            'status'           => 'draft',
            'subtotal'         => $quotation->subtotal,
            'discount'         => $quotation->discount,
            'discount_percent' => $quotation->discount_percent,
            'tax_percent'      => $quotation->tax_percent,
            'tax_amount'       => $quotation->tax_amount,
            'total'            => $quotation->total,
            'terms'            => $quotation->terms,
            'guarantee'        => $quotation->guarantee,
            'payment'          => $quotation->payment,
            'created_by'       => auth()->id(),
        ]);

        foreach ($quotation->items as $item) {
            $invoice->items()->create($item->only((new InvoiceItem())->getFillable()));
        }

        return $invoice;
    }

    public function duplicate(Invoice $invoice)
    {
        $invoice->load('items');
        $new = $invoice->replicate(['invoice_number', 'status']);
        $new->invoice_number = Invoice::generateNumber();
        $new->status         = 'draft';
        $new->date           = now()->toDateString();
        $new->due_date       = now()->addDays(30)->toDateString();
        $new->quotation_id   = null;
        $new->created_by     = auth()->id();
        $new->save();

        foreach ($invoice->items as $item) {
            $new->items()->create($item->only((new InvoiceItem())->getFillable()));
        }

        ActivityLog::log('CREATE', 'Invoices', "Duplicated → {$new->invoice_number}", $new);
        return response()->json([
            'success'  => true,
            'message'  => "Duplicated as {$new->invoice_number}.",
            'redirect' => route('admin.invoices.edit', $new),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function send(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);
        ActivityLog::log('SEND', 'Invoices', "Sent invoice: {$invoice->invoice_number}", $invoice);

        $emailSent = false;
        try {
            $emailSent = $this->autoSendInvoiceEmail($invoice);
        } catch (\Throwable $e) {
            \Log::error('Auto-send invoice email failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as sent.' . ($emailSent ? ' Invoice emailed to customer without descriptions.' : ''),
            'badge'   => $invoice->fresh()->status_badge,
            'email_sent' => $emailSent,
        ]);
    }

    private function autoSendInvoiceEmail(Invoice $invoice, bool $showDesc = false): bool
    {
        $invoice->load('customer', 'items.service', 'items.category', 'createdBy', 'quotation');

        if (empty($invoice->customer->email)) {
            return false;
        }

        $this->applySmtpSettings();

        $subject = $this->parseTemplate(
            setting('invoice_email_subject', 'Invoice {{invoice_number}} from {{company_name}}'),
            $invoice
        );

        $body = $this->parseTemplate(
            setting('invoice_email_body', "Dear {{customer_name}},\n\nPlease find your Invoice {{invoice_number}} attached.\n\nTotal Amount: {{total}}\n\nKindly process the payment as per the invoice terms.\n\nRegards,\n{{company_name}}"),
            $invoice
        );

        Mail::to($invoice->customer->email)->send(new InvoiceMail(
            $invoice,
            $body,
            $subject,
            $showDesc
        ));

        ActivityLog::log('EMAIL', 'Invoices', "Auto-emailed {$invoice->invoice_number} to {$invoice->customer->email}" . ($showDesc ? ' (with descriptions)' : ' (without descriptions)'), $invoice);

        return true;
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        $request->validate([
            'to'        => 'required|email',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string',
            'show_desc' => 'nullable|in:0,1,true,false',
        ]);

        $this->applySmtpSettings();
        $invoice->load('customer', 'items.service', 'items.category', 'createdBy', 'quotation');

        $showDesc = $request->boolean('show_desc', true);

        try {
            Mail::to($request->to)->send(new InvoiceMail(
                $invoice,
                $request->body,
                $request->subject,
                $showDesc
            ));
        } catch (\Throwable $e) {
            \Log::error('Invoice send email failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'to'         => $request->to,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Check SMTP settings (Settings -> Mail). ' . $e->getMessage(),
            ], 500);
        }

        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }

        ActivityLog::log('EMAIL', 'Invoices', "Emailed {$invoice->invoice_number} to {$request->to}" . ($showDesc ? ' (with descriptions)' : ' (without descriptions)'), $invoice);

        return response()->json([
            'success' => true,
            'message' => 'Invoice emailed to ' . $request->to . ($showDesc ? ' with descriptions in PDF.' : ' without descriptions in PDF.'),
            'badge'   => $invoice->fresh()->status_badge,
        ]);
    }

    private function parseTemplate(string $template, Invoice $invoice): string
    {
        $customer    = $invoice->customer;
        $companyName = setting('company_name', config('app.name'));

        $replacements = [
            '{{customer_name}}'    => $customer->name ?? '',
            '{{customer_company}}' => $customer->company_name ?? '',
            '{{customer_email}}'   => $customer->email ?? '',
            '{{customer_phone}}'   => $customer->mobile ?? '',
            '{{invoice_number}}'   => $invoice->invoice_number,
            '{{invoice_date}}'     => $invoice->date->format('d M Y'),
            '{{due_date}}'         => $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A',
            '{{subject}}'          => $invoice->subject ?? '',
            '{{subtotal}}'         => format_currency($invoice->subtotal),
            '{{discount}}'         => format_currency($invoice->discount),
            '{{tax}}'              => format_currency($invoice->tax_amount),
            '{{total}}'            => format_currency($invoice->total),
            '{{company_name}}'     => $companyName,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function applySmtpSettings(): void
    {
        Config::set('mail.mailers.smtp.host',       Setting::get('mail_host'));
        Config::set('mail.mailers.smtp.port',       Setting::get('mail_port'));
        Config::set('mail.mailers.smtp.username',   Setting::get('mail_username'));
        Config::set('mail.mailers.smtp.password',   Setting::get('mail_password'));
        Config::set('mail.mailers.smtp.encryption', Setting::get('mail_encryption'));
        Config::set('mail.from.address',            Setting::get('mail_from_address'));
        Config::set('mail.from.name',               Setting::get('mail_from_name', setting('company_name', config('app.name'))));
    }

    private function invoiceAccountSummary(Invoice $invoice): array
    {
        $paidAmount = Transaction::where('invoice_id', $invoice->id)
            ->where('status', 'cleared')
            ->where('direction', 'income')
            ->where('type', 'payment')
            ->sum('amount');

        $expenseAmount = Expense::where('invoice_id', $invoice->id)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        return [
            'invoice_total' => (float) $invoice->total,
            'paid' => (float) $paidAmount,
            'outstanding' => max(0, (float) $invoice->total - (float) $paidAmount),
            'expense' => (float) $expenseAmount,
            'net_balance' => (float) $paidAmount - (float) $expenseAmount,
        ];
    }

    public function quotationData(Quotation $quotation)
    {
        $quotation->load('customer', 'items.service', 'items.category');

        $groups = [];
        $grouped = $quotation->items->groupBy('sl_no')->sortKeys();
        foreach ($grouped as $sl => $items) {
            $first = $items->first();
            $formMode = $first->area_mode === 'sqft' ? 'sqft'
                      : ($first->area_mode === 'nos' ? 'nos' : 'fixed');

            $services = [];
            foreach ($items as $item) {
                $services[] = [
                    'id'          => $item->service_id,
                    'category_id' => $item->category_id,
                    'title'       => $item->title,
                    'description' => $item->description ?? '',
                    'cat_name'    => $item->category?->name ?? '',
                    'price'       => (float)($item->price ?? 0),
                ];
            }

            $groups[] = [
                'sl_no'        => $sl,
                'mode'         => $formMode,
                'price'        => (float)($first->price ?? 0),
                'total'        => (float)$items->sum('total'),
                'area_value'   => $first->area_value ?? '',
                'rate_per_sft' => $first->rate_per_sft ?? '',
                'area_nos'     => $first->area_nos ?? '',
                'rate_per_nos' => $first->rate_per_nos ?? '',
                'services'     => $services,
            ];
        }

        return response()->json([
            'success'          => true,
            'id'               => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'customer_id'      => $quotation->customer_id,
            'customer'         => $quotation->customer,
            'subject'          => $quotation->subject,
            'due_date'         => $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : null,
            'subtotal'         => (float)$quotation->subtotal,
            'discount'         => (float)$quotation->discount,
            'discount_percent' => (float)$quotation->discount_percent,
            'tax_percent'      => (float)$quotation->tax_percent,
            'tax_amount'       => (float)$quotation->tax_amount,
            'total'            => (float)$quotation->total,
            'terms'            => $quotation->terms,
            'guarantee'        => $quotation->guarantee,
            'payment'          => $quotation->payment,
            'groups'           => $groups,
            'quotation' => [
                'id'               => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'customer_id'      => $quotation->customer_id,
                'customer'         => $quotation->customer,
                'subject'          => $quotation->subject,
                'due_date'         => $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : null,
                'subtotal'         => (float)$quotation->subtotal,
                'discount'         => (float)$quotation->discount,
                'discount_percent' => (float)$quotation->discount_percent,
                'tax_percent'      => (float)$quotation->tax_percent,
                'tax_amount'       => (float)$quotation->tax_amount,
                'total'            => (float)$quotation->total,
                'terms'            => $quotation->terms,
                'guarantee'        => $quotation->guarantee,
                'payment'          => $quotation->payment,
                'groups'           => $groups,
            ],
        ]);
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'quotation_id'     => 'nullable|exists:quotations,id',
            'customer_id'      => 'required|exists:customers,id',
            'subject'          => 'nullable|string|max:255',
            'date'             => 'required|date',
            'due_date'         => 'nullable|date|after_or_equal:date',
            'status'           => 'required|in:draft,sent,paid,partial,overdue,cancelled',
            'discount'         => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent'      => 'nullable|numeric|min:0|max:100',
            'terms'            => 'nullable|string',
            'guarantee'        => 'nullable|string',
            'payment'          => 'nullable|string',
        ]);
    }

    private function syncGroups(Invoice $invoice, array $groups): void
    {
        $invoice->items()->delete();
        $sortOrder = 0;

        foreach ($groups as $slNo => $group) {
            $rawMode = $group['area_mode'] ?? 'fixed';
            $dbMode  = match($rawMode) {
                'sqft'  => 'sqft',
                'nos'   => 'nos',
                default => 'manual',
            };

            $price = $this->resolveGroupPrice($group, $rawMode);
            if ($rawMode === 'fixed' && $price <= 0) {
                $price = collect($group['services'] ?? [])->sum(fn ($svc) => (float) ($svc['price'] ?? 0));
            }

            $services = array_values(array_filter(
                $group['services'] ?? [],
                fn($s) => $this->isValidServiceRow($s)
            ));

            if (empty($services) && !$this->hasGroupValue($group)) {
                continue;
            }

            if (empty($services)) {
                $services = [['service_id' => null, 'category_id' => null, 'title' => 'Item', 'description' => null]];
            }

            foreach ($services as $sIdx => $svc) {
                $rowPrice = $sIdx === 0 ? $price : 0;

                $invoice->items()->create([
                    'service_id'   => $svc['service_id'] ?: null,
                    'category_id'  => $svc['category_id'] ?: null,
                    'title'        => $svc['title'],
                    'description'  => $svc['description'] ?? null,
                    'price'        => $rowPrice,
                    'qty'          => 1,
                    'total'        => $rowPrice,
                    'sort_order'   => $sortOrder++,
                    'sl_no'        => (int)($group['sl_no'] ?? ($slNo + 1)),
                    'option_no'    => 1,
                    'area_mode'    => $dbMode,
                    'area_value'   => $dbMode === 'sqft' ? ((float)($group['area_value'] ?? 0) ?: null) : null,
                    'rate_per_sft' => $dbMode === 'sqft' ? ((float)($group['rate_per_sft'] ?? 0) ?: null) : null,
                    'area_nos'     => $dbMode === 'nos'  ? ((float)($group['area_nos'] ?? 0) ?: null) : null,
                    'rate_per_nos' => $dbMode === 'nos'  ? ((float)($group['rate_per_nos'] ?? 0) ?: null) : null,
                ]);
            }
        }
    }

    private function resolveGroupPrice(array $group, string $mode): float
    {
        $override = (float)($group['price_override'] ?? 0);

        if ($mode === 'sqft') {
            $calc = (float)($group['area_value'] ?? 0) * (float)($group['rate_per_sft'] ?? 0);
            return $override > 0 ? $override : $calc;
        }

        if ($mode === 'nos') {
            $calc = (float)($group['area_nos'] ?? 0) * (float)($group['rate_per_nos'] ?? 0);
            return $override > 0 ? $override : $calc;
        }

        return (float)($group['price'] ?? 0);
    }

    private function isValidServiceRow(array $service): bool
    {
        return !empty(trim((string)($service['title'] ?? '')))
            || !empty(trim((string)($service['description'] ?? '')))
            || !empty(trim((string)($service['service_id'] ?? '')))
            || ((float)($service['price'] ?? 0) > 0);
    }

    private function hasGroupValue(array $group): bool
    {
        return ((float)($group['price'] ?? 0) > 0)
            || ((float)($group['price_override'] ?? 0) > 0)
            || ((float)($group['area_value'] ?? 0) > 0)
            || ((float)($group['rate_per_sft'] ?? 0) > 0)
            || ((float)($group['area_nos'] ?? 0) > 0)
            || ((float)($group['rate_per_nos'] ?? 0) > 0);
    }

    private function recalcTotals(Invoice $invoice, array $data): void
    {
        $subtotal = (float) $invoice->items()->sum('total');
        $discount = 0.0;
        $discPct  = (float)($data['discount_percent'] ?? 0);

        if (array_key_exists('discount', $data) && $data['discount'] !== null && $data['discount'] !== '' && (float)$data['discount'] > 0) {
            $discount = (float)$data['discount'];
            if ($subtotal > 0) {
                $discPct = round(($discount / $subtotal) * 100, 2);
            }
        } elseif ($discPct > 0) {
            $discount = round($subtotal * $discPct / 100, 2);
        }

        $taxPct    = (float)($data['tax_percent'] ?? 0);
        $taxAmount = round(max(0, ($subtotal - $discount)) * $taxPct / 100, 2);
        $total     = max(0, $subtotal - $discount + $taxAmount);

        $invoice->update([
            'subtotal'         => $subtotal,
            'discount'         => $discount,
            'discount_percent' => $discPct,
            'tax_percent'      => $taxPct,
            'tax_amount'       => $taxAmount,
            'total'            => $total,
        ]);
    }
}
