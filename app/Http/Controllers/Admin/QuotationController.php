<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\QuotationMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with('customer', 'createdBy', 'invoice');

        if ($s = $request->search) {
            $query->where(fn($q) => $q->where('quotation_number', 'like', "%$s%")
                ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%$s%"))
                ->orWhereHas('invoice', fn($q3) => $q3->where('invoice_number', 'like', "%$s%")));
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $quotations = $query->latest()->paginate(10)->withQueryString();
        return view('admin.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers  = Customer::where('status', 'active')->orderBy('name')->get();
        $categories = ServiceCategory::with(['services' => fn($q) => $q->where('status', 'active')->orderBy('title')])
                        ->where('status', 'active')->orderBy('name')->get();
        $quotation  = null;
        $defaults   = [
            'guarantee' => setting('quotation_guarantee', ''),
            'terms'     => setting('quotation_terms',     ''),
            'payment'   => setting('quotation_payment',   ''),
        ];
        return view('admin.quotations.form', compact('customers', 'categories', 'quotation', 'defaults'));
    }

    public function store(Request $request)
    {
        $data = $this->validateQuotation($request);
        $data['quotation_number'] = Quotation::generateNumber();
        $data['created_by']       = auth()->id();

        $quotation = Quotation::create($data);
        $this->syncGroups($quotation, $request->input('groups', []));
        $this->recalcTotals($quotation, $data);

        ActivityLog::log('CREATE', 'Quotations', "Created quotation: {$quotation->quotation_number}", $quotation);
        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->quotation_number} created.");
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('customer', 'items.service', 'items.category', 'createdBy');

        $emailDefaults = [
            'subject' => $this->parseTemplate(
                setting('quotation_email_subject', 'Quotation {{quotation_number}} from {{company_name}}'),
                $quotation
            ),
            'body' => $this->parseTemplate(
                setting('quotation_email_body', "Dear {{customer_name}},\n\nPlease find your Quotation {{quotation_number}} attached.\n\nTotal Amount: {{total}}\n\nKindly review and revert at your earliest convenience.\n\nRegards,\n{{company_name}}"),
                $quotation
            ),
        ];

        return view('admin.quotations.show', compact('quotation', 'emailDefaults'));
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items');
        $customers  = Customer::where('status', 'active')->orderBy('name')->get();
        $categories = ServiceCategory::with(['services' => fn($q) => $q->where('status', 'active')->orderBy('title')])
                        ->where('status', 'active')->orderBy('name')->get();
        // Pass existing values as defaults so the form pre-fills them
        $defaults = [
            'guarantee' => $quotation->guarantee ?? '',
            'terms'     => $quotation->terms     ?? '',
            'payment'   => $quotation->payment   ?? '',
        ];
        return view('admin.quotations.form', compact('quotation', 'customers', 'categories', 'defaults'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $this->validateQuotation($request);
        $quotation->update($data);
        $this->syncGroups($quotation, $request->input('groups', []));
        $this->recalcTotals($quotation, $data);

        ActivityLog::log('UPDATE', 'Quotations', "Updated quotation: {$quotation->quotation_number}", $quotation);
        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        ActivityLog::log('DELETE', 'Quotations', "Deleted quotation: {$quotation->quotation_number}", $quotation);
        $quotation->delete();
        return response()->json(['success' => true, 'message' => 'Quotation deleted.']);
    }

    public function print(Quotation $quotation)
    {
        $quotation->load('customer', 'items.service', 'items.category', 'createdBy');
        return view('admin.quotations.print', compact('quotation'));
    }

    public function pdfDownload(Request $request, Quotation $quotation)
    {
        $quotation->load('customer', 'items.service', 'items.category', 'createdBy');
        $showDesc = $request->boolean('desc', true);
        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation', 'showDesc'))
                  ->setPaper('a4', 'portrait');
        $filename = $quotation->quotation_number . '.pdf';
        return $pdf->download($filename);
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate(['status' => 'required|in:draft,sent,accepted,approved,rejected,expired']);
        
        $oldStatus = $quotation->status;
        $newStatus = $request->status;
        
        $quotation->update(['status' => $newStatus]);
        ActivityLog::log('STATUS_CHANGE', 'Quotations', "Status: {$oldStatus} → {$newStatus}: {$quotation->quotation_number}", $quotation);

        // Auto-send email when status changes to "sent" (if enabled in settings)
        $emailSent = false;
        $emailMessage = '';
        
        if ($newStatus === 'sent' && setting('quotation_auto_send_enabled', '') == '1') {
            try {
                $emailSent = $this->autoSendQuotationEmail($quotation);
                if ($emailSent) {
                    $emailMessage = ' Quotation emailed to ' . ($quotation->customer->email ?? 'customer');
                }
            } catch (\Throwable $e) {
                // Log error but don't fail the status update
                \Log::error('Auto-send quotation email failed: ' . $e->getMessage());
            }
        }

        $invoiceRedirect = null;
        if ($newStatus === 'approved') {
            $invoice = $this->convertToInvoice($quotation);
            $invoiceRedirect = route('admin.invoices.show', $invoice);
        }

        return response()->json([
            'success'         => true,
            'message'         => $newStatus === 'approved' 
                ? 'Quotation approved & invoice created.' 
                : 'Status updated.' . ($emailSent ? ' Email sent to customer.' : ''),
            'badge'           => $quotation->fresh()->status_badge,
            'invoice_redirect' => $invoiceRedirect,
            'email_sent'      => $emailSent,
        ]);
    }

    /**
     * Automatically send quotation email using settings template
     */
    private function autoSendQuotationEmail(Quotation $quotation): bool
    {
        // Check if customer has email
        if (empty($quotation->customer->email)) {
            return false;
        }

        // Load relations
        $quotation->load(['customer', 'items']);

        // Apply SMTP settings
        $this->applySmtpSettings();

        // Build subject and body from settings with placeholder parsing
        $subject = $this->parseTemplate(
            setting('quotation_email_subject', 'Quotation {{quotation_number}} from {{company_name}}'),
            $quotation
        );
        
        $body = $this->parseTemplate(
            setting('quotation_email_body', "Dear {{customer_name}},\n\nPlease find your Quotation {{quotation_number}} attached.\n\nTotal Amount: {{total}}\n\nKindly review and revert at your earliest convenience.\n\nRegards,\n{{company_name}}"),
            $quotation
        );

        $showDesc = setting('quotation_email_include_desc', '1') == '1';

        // Send email
        Mail::to($quotation->customer->email)->send(new \App\Mail\QuotationAutoMail(
            $quotation,
            $body,
            $subject,
            $showDesc
        ));

        // Handle CC if configured
        $ccEmails = setting('quotation_email_cc', '');
        if ($ccEmails) {
            $ccArray = array_map('trim', explode(',', $ccEmails));
            foreach ($ccArray as $cc) {
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($cc)->send(new \App\Mail\QuotationAutoMail(
                        $quotation,
                        $body,
                        $subject,
                        $showDesc
                    ));
                }
            }
        }

        ActivityLog::log('EMAIL', 'Quotations', "Auto-emailed {$quotation->quotation_number} to {$quotation->customer->email}", $quotation);

        return true;
    }

    /**
     * Parse template placeholders for quotation emails
     */
    private function parseTemplate(string $template, Quotation $quotation): string
    {
        $customer    = $quotation->customer;
        $companyName = setting('company_name', config('app.name'));
        $currency    = setting('currency_symbol', '₹');

        $replacements = [
            '{{customer_name}}'      => $customer->name ?? '',
            '{{customer_company}}'   => $customer->company_name ?? '',
            '{{customer_email}}'     => $customer->email ?? '',
            '{{customer_phone}}'     => $customer->mobile ?? '',
            '{{quotation_number}}'   => $quotation->quotation_number,
            '{{quotation_date}}'     => $quotation->date->format('d M Y'),
            '{{valid_until}}'        => $quotation->valid_until ? $quotation->valid_until->format('d M Y') : 'N/A',
            '{{subject}}'            => $quotation->subject ?? '',
            '{{subtotal}}'           => $currency . number_format($quotation->subtotal, 2),
            '{{discount}}'           => $currency . number_format($quotation->discount, 2),
            '{{tax}}'                => $currency . number_format($quotation->tax_amount, 2),
            '{{total}}'              => $currency . number_format($quotation->total, 2),
            '{{company_name}}'       => $companyName,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function convertToInvoice(Quotation $quotation): Invoice
    {
        // Avoid duplicate invoices for the same quotation
        $existing = Invoice::where('quotation_id', $quotation->id)->first();
        if ($existing) return $existing;

        $quotation->loadMissing('items');

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

        // Copy all quotation items into invoice items
        foreach ($quotation->items as $item) {
            $invoice->items()->create([
                'service_id'   => $item->service_id,
                'category_id'  => $item->category_id,
                'title'        => $item->title,
                'description'  => $item->description,
                'qty'          => 1,
                'rate'         => $item->total,
                'total'        => $item->total,
                'sort_order'   => $item->sort_order,
                'sl_no'        => $item->sl_no,
                'option_no'    => $item->option_no,
                'area_mode'    => $item->area_mode,
                'area_value'   => $item->area_value,
                'rate_per_sft' => $item->rate_per_sft,
                'area_nos'     => $item->area_nos,
                'rate_per_nos' => $item->rate_per_nos,
            ]);
        }

        return $invoice;
    }

    public function convertInvoice(Quotation $quotation)
    {
        $existing = Invoice::where('quotation_id', $quotation->id)->first();
        if ($existing) {
            return response()->json([
                'success'  => true,
                'message'  => "Invoice {$existing->invoice_number} already exists.",
                'redirect' => route('admin.invoices.show', $existing),
            ]);
        }

        return response()->json([
            'success'  => true,
            'message'  => "Loading quotation details into invoice form...",
            'redirect' => route('admin.invoices.create', ['quotation_id' => $quotation->id]),
        ]);
    }

    public function duplicate(Quotation $quotation)
    {
        $quotation->load('items');
        $new = $quotation->replicate(['quotation_number', 'status']);
        $new->quotation_number = Quotation::generateNumber();
        $new->status           = 'draft';
        $new->date             = now()->toDateString();
        $new->created_by       = auth()->id();
        $new->save();

        foreach ($quotation->items as $item) {
            $new->items()->create($item->only($item->getFillable()));
        }

        ActivityLog::log('CREATE', 'Quotations', "Duplicated → {$new->quotation_number}", $new);
        return response()->json([
            'success'  => true,
            'message'  => "Duplicated as {$new->quotation_number}.",
            'redirect' => route('admin.quotations.edit', $new),
        ]);
    }

    public function send(Quotation $quotation)
    {
        $quotation->update(['status' => 'sent']);
        ActivityLog::log('SEND', 'Quotations', "Sent quotation: {$quotation->quotation_number}", $quotation);
        return response()->json(['success' => true, 'message' => 'Quotation marked as sent.']);
    }

    public function sendEmail(Request $request, Quotation $quotation)
    {
        $request->validate([
            'to'        => 'required|email',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string',
            'show_desc' => 'nullable|in:0,1,true,false',
        ]);

        $this->applySmtpSettings();
        $quotation->load('customer', 'items.service', 'items.category', 'createdBy');

        $showDesc = $request->boolean('show_desc', true);

        try {
            Mail::to($request->to)->send(new QuotationMail(
                $quotation,
                $request->body,
                $request->subject,
                $showDesc
            ));
        } catch (\Throwable $e) {
            \Log::error('Quotation send email failed: ' . $e->getMessage(), [
                'quotation_id' => $quotation->id,
                'to'           => $request->to,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Check SMTP settings (Settings → Mail). ' . $e->getMessage(),
            ], 500);
        }

        if ($quotation->status === 'draft') {
            $quotation->update(['status' => 'sent']);
        }

        ActivityLog::log('EMAIL', 'Quotations', "Emailed {$quotation->quotation_number} to {$request->to}" . ($showDesc ? ' (with descriptions)' : ' (without descriptions)'), $quotation);

        return response()->json([
            'success' => true,
            'message' => 'Quotation emailed to ' . $request->to . ($showDesc ? ' with descriptions in PDF.' : ' without descriptions in PDF.'),
            'badge'   => $quotation->fresh()->status_badge,
        ]);
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

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validateQuotation(Request $request): array
    {
        return $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'subject'          => 'nullable|string|max:255',
            'date'             => 'required|date',
            'valid_until'      => 'nullable|date|after_or_equal:date',
            'status'           => 'required|in:draft,sent,accepted,approved,rejected,expired',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent'      => 'nullable|numeric|min:0|max:100',
            'terms'            => 'nullable|string',
            'guarantee'        => 'nullable|string',
            'payment'          => 'nullable|string',
        ]);
    }

    /**
     * Expand groups[] (from the form) into flat quotation_items rows.
     *
     * Form structure:
     *   groups[N][area_mode]      = fixed|sqft|nos
     *   groups[N][sl_no]          = 1,2,3…
     *   groups[N][price]          = group total (fixed or override)
     *   groups[N][area_value]     = total sqft  (sqft mode)
     *   groups[N][rate_per_sft]   = rate/sqft   (sqft mode)
     *   groups[N][area_nos]       = no. of units (nos mode)
     *   groups[N][rate_per_nos]   = rate/unit    (nos mode)
     *   groups[N][services][M][service_id]
     *   groups[N][services][M][category_id]
     *   groups[N][services][M][title]
     *   groups[N][services][M][description]
     *
     * Each group becomes one item row per service (sharing the group price on
     * the first service row; subsequent rows get price=0 so totals stay correct).
     * If a group has no services, one placeholder row is created.
     */
    private function syncGroups(Quotation $quotation, array $groups): void
    {
        $quotation->items()->delete();

        $sortOrder = 0;

        foreach ($groups as $slNo => $group) {
            $rawMode = $group['area_mode'] ?? 'fixed';

            // Map form mode → DB enum (manual = fixed/no area calc)
            $dbMode = match($rawMode) {
                'sqft'  => 'sqft',
                'nos'   => 'nos',
                default => 'manual',
            };

            // Resolve the group price
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

            // If no services but the group has pricing/area data, create a single unnamed row
            if (empty($services)) {
                $services = [['service_id' => null, 'category_id' => null, 'title' => 'Item', 'description' => null]];
            }

            foreach ($services as $sIdx => $svc) {
                // Only the first service in the group carries the price
                $rowPrice = $sIdx === 0 ? $price : 0;

                $quotation->items()->create([
                    'service_id'   => $svc['service_id']  ?: null,
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

        return (float)($group['price'] ?? 0); // fixed mode
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

    private function recalcTotals(Quotation $quotation, array $data): void
    {
        $subtotal  = $quotation->items()->sum('total');
        $discPct   = (float)($data['discount_percent'] ?? 0);
        $discount  = round($subtotal * $discPct / 100, 2);
        $taxPct    = (float)($data['tax_percent'] ?? 0);
        $taxAmount = round(($subtotal - $discount) * $taxPct / 100, 2);
        $total     = $subtotal - $discount + $taxAmount;

        $quotation->update([
            'subtotal'   => $subtotal,
            'discount'   => $discount,
            'tax_amount' => $taxAmount,
            'total'      => $total,
        ]);
    }
}
