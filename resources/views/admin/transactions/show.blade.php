@extends('layouts.app')
@section('title', $transaction->transaction_number)

@push('styles')
<style>
.txn-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:20px;margin-bottom:16px}
.meta-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:4px}
.meta-value{font-size:14px;font-weight:650;color:#1e293b}
.big-amount{font-size:28px;font-weight:900;letter-spacing:-.4px}
.detail-row{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #f1f5f9}
.detail-row:last-child{border-bottom:0}.detail-row span:first-child{font-size:12px;color:#64748b}.detail-row span:last-child{font-size:13px;font-weight:700;text-align:right}
.timeline-item{display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9}.timeline-item:last-child{border-bottom:0}
.timeline-dot{width:9px;height:9px;border-radius:50%;background:var(--primary,#4f46e5);margin-top:6px;flex-shrink:0}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">{{ $transaction->transaction_number }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Payments</a></li>
                <li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(auth()->user()->hasPermission('transactions.edit'))
        <button type="button" class="btn btn-success" onclick="sendReceipt()">
            <i class="bi bi-envelope me-1"></i>Send Receipt
        </button>
        <a href="{{ route('admin.transactions.receipt', $transaction) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Preview
        </a>
        <a href="{{ route('admin.transactions.receipt', $transaction) }}?download=1" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i>Download
        </a>
        @if(auth()->user()->hasPermission('expenses.create'))
        <a href="{{ route('admin.expenses.create', ['transaction_id' => $transaction->id]) }}" class="btn btn-outline-danger">
            <i class="bi bi-wallet2 me-1"></i>Add Expense
        </a>
        @endif
        <a href="{{ route('admin.transactions.edit', $transaction) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="txn-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="meta-label">Paid Amount</div>
                    <div class="big-amount text-success">+{{ format_currency($transaction->amount) }}</div>
                </div>
                <div class="text-end">
                    <div class="mb-2">{!! $transaction->status_badge !!}</div>
                    <div><span class="badge" style="background:#dbeafe;color:#1d4ed8;">Payment Receipt</span></div>
                    <div class="text-muted mt-2" id="receiptSentInfo" style="font-size:12px;">
                        @if($transaction->receipt_sent_at)
                            Receipt sent {{ $transaction->receipt_sent_at->format('d M Y, h:i A') }}
                        @else
                            Receipt not sent
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="txn-card">
            <div class="meta-label mb-3">Payment Details</div>
            <div class="detail-row"><span>Date</span><span>{{ $transaction->transaction_date->format('d M Y') }}</span></div>
            <div class="detail-row"><span>Payment Method</span><span>{{ str_replace('_', ' ', ucfirst($transaction->payment_method)) }}</span></div>
            <div class="detail-row"><span>Reference</span><span>{{ $transaction->reference_no ?: '—' }}</span></div>
            <div class="detail-row"><span>Receipt Sent To</span><span>{{ $transaction->receipt_sent_to ?: '—' }}</span></div>
            <div class="detail-row"><span>Created By</span><span>{{ $transaction->createdBy?->name ?? '—' }}</span></div>
            <div class="detail-row"><span>Created</span><span>{{ $transaction->created_at->format('d M Y, h:i A') }}</span></div>
        </div>

        @if($accountSummary)
        <div class="txn-card">
            <div class="meta-label mb-3">Invoice Balance</div>
            <div class="detail-row"><span>Invoice Total</span><span>{{ format_currency($accountSummary['invoice_total']) }}</span></div>
            <div class="detail-row"><span>Paid / Collected</span><span class="text-success">{{ format_currency($accountSummary['paid']) }}</span></div>
            <div class="detail-row"><span>Outstanding</span><span class="text-danger">{{ format_currency($accountSummary['outstanding']) }}</span></div>
            <div class="detail-row"><span>Invested Expense</span><span class="text-danger">{{ format_currency($accountSummary['expense']) }}</span></div>
            <div class="detail-row"><span>Net After Expense</span><span class="{{ $accountSummary['net_balance'] >= 0 ? 'text-success' : 'text-danger' }}">{{ format_currency($accountSummary['net_balance']) }}</span></div>
        </div>
        @endif

        @if($transaction->notes)
        <div class="txn-card">
            <div class="meta-label mb-2">Notes</div>
            <div style="font-size:13px;color:#475569;white-space:pre-line;line-height:1.7;">{{ $transaction->notes }}</div>
        </div>
        @endif

        @if($invoicePayments->isNotEmpty())
        <div class="txn-card">
            <div class="meta-label mb-3">Invoice Payment History</div>
            @foreach($invoicePayments as $payment)
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between gap-3">
                        <a href="{{ route('admin.transactions.show', $payment) }}" class="fw-700 text-decoration-none">{{ $payment->transaction_number }}</a>
                        <span class="text-success fw-800">+{{ format_currency($payment->amount) }}</span>
                    </div>
                    <div class="text-muted" style="font-size:12px;">{{ $payment->transaction_date->format('d M Y') }} · {{ str_replace('_',' ', ucfirst($payment->payment_method)) }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="txn-card">
            <div class="meta-label mb-3">Customer</div>
            @if($transaction->customer)
            <div class="meta-value">{{ $transaction->customer->name }}</div>
            @if($transaction->customer->company_name)<div class="text-muted" style="font-size:13px;">{{ $transaction->customer->company_name }}</div>@endif
            @if($transaction->customer->email)<div style="font-size:13px;margin-top:8px;"><i class="bi bi-envelope me-1 text-muted"></i>{{ $transaction->customer->email }}</div>@endif
            @if($transaction->customer->mobile)<div style="font-size:13px;"><i class="bi bi-phone me-1 text-muted"></i>{{ $transaction->customer->mobile }}</div>@endif
            @else
            <div class="text-muted">No customer linked.</div>
            @endif
        </div>

        @if($transaction->invoice)
        <div class="txn-card">
            <div class="meta-label mb-3">Linked Invoice</div>
            <a href="{{ route('admin.invoices.show', $transaction->invoice) }}" class="meta-value text-decoration-none">
                {{ $transaction->invoice->invoice_number }}
            </a>
            @if($transaction->invoice->quotation)
            <div class="text-muted" style="font-size:13px;">Quotation: {{ $transaction->invoice->quotation->quotation_number }}</div>
            @endif
            <div class="detail-row"><span>Status</span><span>{!! $transaction->invoice->status_badge !!}</span></div>
            <div class="detail-row"><span>Total</span><span>{{ format_currency($transaction->invoice->total) }}</span></div>
            <div class="detail-row"><span>Date</span><span>{{ $transaction->invoice->date->format('d M Y') }}</span></div>
            @if($transaction->invoice->due_date)
            <div class="detail-row"><span>Due</span><span>{{ $transaction->invoice->due_date->format('d M Y') }}</span></div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function doSendReceipt(email) {
    $.post('{{ route("admin.transactions.send-receipt", $transaction) }}', { to: email })
        .done(r => {
            document.getElementById('receiptSentInfo').textContent = 'Receipt sent ' + r.sent_at;
            Swal.fire({ icon:'success', text:r.message, timer:2200, showConfirmButton:false });
        })
        .fail(x => Swal.fire('Error', x.responseJSON?.message || 'Failed to send receipt.', 'error'));
}

function sendReceipt() {
    const customerEmail = @json($transaction->customer?->email ?? '');

    if (customerEmail) {
        Swal.fire({
            title: 'Send payment receipt?',
            text: 'This will send the receipt directly to ' + customerEmail + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Send Now'
        }).then(result => {
            if (!result.isConfirmed) return;
            doSendReceipt(customerEmail);
        });
        return;
    }

    Swal.fire({
        title: 'Enter customer email',
        input: 'email',
        inputPlaceholder: 'customer@example.com',
        showCancelButton: true,
        confirmButtonText: 'Send Receipt',
        preConfirm: email => {
            if (!email) {
                Swal.showValidationMessage('Please enter an email address.');
                return false;
            }
            return email;
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        doSendReceipt(result.value);
    });
}
</script>
@endpush
