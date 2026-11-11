<?php

namespace App\Mail;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationAutoMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;

    public function __construct(
        public Quotation $quotation,
        public string $body,
        string $subject,
        public bool $showDesc = true
    ) {
        $this->emailSubject = $subject;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->emailSubject);
    }

    public function content(): Content
    {
        return new Content(html: $this->buildHtmlContent());
    }

    public function attachments(): array
    {
        $quotation = $this->quotation;
        $showDesc  = $this->showDesc;

        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation', 'showDesc'))
                  ->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $this->quotation->quotation_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }

    /**
     * Build HTML email content from settings template
     */
    private function buildHtmlContent(): string
    {
        // Get template from settings
        $template = setting('quotation_email_template', 'default');
        
        // If using default template, use the existing blade view
        if ($template === 'default') {
            return view('emails.quotation', [
                'quotation'  => $this->quotation,
                'body'       => $this->body,
                'subject'    => $this->emailSubject,
                'showDesc'   => $this->showDesc,
            ])->render();
        }

        // Parse custom template with placeholders
        $html = $this->parseTemplate($template);
        
        return $html;
    }

    /**
     * Parse custom email template with placeholders
     */
    private function parseTemplate(string $template): string
    {
        $quotation = $this->quotation;
        $customer  = $quotation->customer;
        $companyName = setting('company_name', config('app.name'));
        $currency    = setting('currency_symbol', '₹');
        $primaryColor = setting('theme_color', '#3B82F6');

        // Build items HTML
        $itemsHtml = $this->buildSimpleItemsTable($quotation, $currency);
        
        // Build totals HTML
        $totalsHtml = $this->buildSimpleTotals($quotation, $currency);
        
        // Get simple template
        $simpleTemplate = $this->getSimpleTemplate($primaryColor);
        
        // Replace placeholders
        $replacements = [
            '{{primary_color}}'      => $primaryColor,
            '{{customer_name}}'      => $customer->name ?? 'Valued Customer',
            '{{customer_company}}'   => $customer->company_name ?? '',
            '{{customer_email}}'     => $customer->email ?? '',
            '{{customer_phone}}'     => $customer->mobile ?? '',
            '{{quotation_number}}'   => $quotation->quotation_number,
            '{{quotation_date}}'     => $quotation->date->format('d M Y'),
            '{{valid_until}}'        => $quotation->valid_until ? $quotation->valid_until->format('d M Y') : 'N/A',
            '{{body}}'               => nl2br(e($this->body)),
            '{{items_table}}'        => $itemsHtml,
            '{{totals}}'             => $totalsHtml,
            '{{company_name}}'       => $companyName,
            '{{company_address}}'    => setting('company_address', ''),
            '{{terms}}'              => nl2br(e($quotation->terms ?? '')),
            '{{payment_terms}}'      => nl2br(e($quotation->payment ?? '')),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $simpleTemplate);
    }

    /**
     * Get simple responsive template
     */
    private function getSimpleTemplate(string $primaryColor): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Quotation {{quotation_number}}</title>
    <style>
        /* Reset styles for email clients */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            background: #f4f6f9;
            margin: 0;
            padding: 20px 0;
            -webkit-text-size-adjust: 100%;
        }
        
        /* Main container */
        .email-container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        /* Header */
        .header {
            background: {{primary_color}};
            padding: 30px 24px;
            text-align: center;
        }
        
        .header h1 {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin: 0 0 8px 0;
            letter-spacing: -0.3px;
        }
        
        .quote-number {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            margin-top: 8px;
        }
        
        /* Content */
        .content {
            padding: 32px 24px;
        }
        
        /* Info sections - stacked on mobile */
        .info-section {
            margin-bottom: 24px;
        }
        
        .info-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 8px;
        }
        
        .info-value {
            font-size: 15px;
            color: #212529;
            line-height: 1.6;
        }
        
        .divider {
            height: 1px;
            background: #e9ecef;
            margin: 20px 0;
        }
        
        /* Message box */
        .message-box {
            background: #f8f9fa;
            border-left: 3px solid {{primary_color}};
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }
        
        .message-box p {
            margin: 0;
            color: #495057;
            font-size: 14px;
            line-height: 1.6;
        }
        
        /* Items table - responsive */
        .table-wrapper {
            overflow-x: auto;
            margin: 24px 0;
            -webkit-overflow-scrolling: touch;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 300px;
        }
        
        .items-table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f3f5;
            color: #212529;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table th:nth-child(3),
        .items-table td:nth-child(3),
        .items-table th:nth-child(4),
        .items-table td:nth-child(4) {
            text-align: center;
        }
        
        /* Totals box */
        .totals-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .total-row.grand-total {
            border-top: 2px solid #dee2e6;
            margin-top: 8px;
            padding-top: 12px;
            font-weight: 700;
            font-size: 16px;
            color: {{primary_color}};
        }
        
        /* Terms section */
        .terms-section {
            background: #fff9e6;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
            font-size: 13px;
            color: #856404;
        }
        
        .terms-section strong {
            display: block;
            margin-bottom: 8px;
        }
        
        /* Button */
        .btn {
            display: inline-block;
            background: {{primary_color}};
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background: #f8f9fa;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            margin: 8px 0;
        }
        
        /* Utility classes */
        .text-center {
            text-align: center;
        }
        
        .mb-3 {
            margin-bottom: 16px;
        }
        
        /* Mobile optimizations */
        @media only screen and (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .content {
                padding: 24px 16px;
            }
            
            .header {
                padding: 24px 16px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 6px;
                font-size: 12px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Quotation</h1>
            <div class="quote-number">{{quotation_number}}</div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Customer Info -->
            <div class="info-section">
                <div class="info-label">BILL TO</div>
                <div class="info-value">
                    <strong>{{customer_name}}</strong><br>
                    {{customer_company}}<br>
                    {{customer_email}}<br>
                    {{customer_phone}}
                </div>
            </div>
            
            <!-- Quote Info -->
            <div class="info-section">
                <div class="info-label">QUOTE DETAILS</div>
                <div class="info-value">
                    Date: {{quotation_date}}<br>
                    Valid Until: {{valid_until}}
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Message -->
            <div class="message-box">
                <p>{{body}}</p>
            </div>
            
            <!-- Items Table -->
            <div class="table-wrapper">
                {{items_table}}
            </div>
            
            <!-- Totals -->
            <div class="totals-box">
                {{totals}}
            </div>
            
            <!-- Terms & Conditions -->
            <div class="terms-section">
                <strong>📋 Terms & Payment</strong>
                <div>{{terms}}</div>
                <div style="margin-top: 8px;"><strong>Payment Terms:</strong> {{payment_terms}}</div>
            </div>
            
            <!-- Download Button -->
            <div class="text-center mb-3">
                <a href="#" class="btn">📎 Download PDF</a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>{{company_name}}</strong></p>
            <p>{{company_address}}</p>
            <p style="font-size: 11px;">This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Build simple responsive items table
     */
    private function buildSimpleItemsTable($quotation, string $currency): string
    {
        $html = '<table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($quotation->items as $item) {
            $html .= '<tr>
                <td style="font-weight: 500;">' . e($item->title) . '</td>
                <td style="font-size: 12px; color: #6c757d;">' . e($item->description ?? '-') . '</td>
                <td>' . $currency . number_format($item->price, 2) . '</td>
                <td>' . $item->qty . '</td>
                <td style="font-weight: 500;">' . $currency . number_format($item->total, 2) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>';
        
        return $html;
    }

    /**
     * Build simple totals section
     */
    private function buildSimpleTotals($quotation, string $currency): string
    {
        $html = '<div class="total-row">
            <span>Subtotal</span>
            <span>' . $currency . number_format($quotation->subtotal, 2) . '</span>
        </div>';
        
        if ($quotation->discount > 0) {
            $html .= '<div class="total-row">
                <span>Discount (' . $quotation->discount_percent . '%)</span>
                <span style="color: #dc2626;">-' . $currency . number_format($quotation->discount, 2) . '</span>
            </div>';
        }
        
        if ($quotation->tax_amount > 0) {
            $html .= '<div class="total-row">
                <span>Tax (' . $quotation->tax_percent . '%)</span>
                <span style="color: #059669;">+' . $currency . number_format($quotation->tax_amount, 2) . '</span>
            </div>';
        }
        
        $html .= '<div class="total-row grand-total">
            <span>Total Amount</span>
            <span>' . $currency . number_format($quotation->total, 2) . '</span>
        </div>';
        
        return $html;
    }
}