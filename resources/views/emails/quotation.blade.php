<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="color-scheme" content="light">
  <title>Quotation | {{ $quotation->quotation_number }}</title>
  <style>
    /* Clean, professional reset */
    body {
      margin: 0;
      padding: 0;
      background-color: #F3F4F6;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
      -webkit-text-size-adjust: 100%;
      line-height: 1.6;
      color: #1F2937;
    }

    /* Main wrapper with subtle texture via background */
    .email-wrapper {
      width: 100%;
      background-color: #F3F4F6;
      padding: 40px 20px;
      box-sizing: border-box;
    }

    /* Container card: soft shadow, generous radius */
    .email-container {
      max-width: 620px;
      margin: 0 auto;
      background-color: #FFFFFF;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.05), 0 8px 16px -6px rgba(0, 0, 0, 0.02);
      border: 1px solid rgba(229, 231, 235, 0.5);
    }

    /* Header with refined gradient and subtle pattern overlay */
    .header {
      background: linear-gradient(125deg, {{ setting('theme_color', '#4F46E5') }} 0%, #6366F1 50%, #3B82F6 100%);
      padding: 44px 36px 40px;
      text-align: center;
      color: #FFFFFF;
      position: relative;
      overflow: hidden;
    }

    /* Decorative soft light element */
    .header::after {
      content: '';
      position: absolute;
      top: -30px;
      right: -30px;
      width: 160px;
      height: 160px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 50%;
      pointer-events: none;
    }

    .header::before {
      content: '';
      position: absolute;
      bottom: -40px;
      left: -20px;
      width: 140px;
      height: 140px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      pointer-events: none;
    }

    .header-content {
      position: relative;
      z-index: 2;
    }

    .company-name {
      margin: 0;
      font-size: 26px;
      font-weight: 700;
      letter-spacing: -0.4px;
      line-height: 1.2;
      text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .document-badge {
      display: inline-block;
      margin-top: 14px;
      background: rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      border-radius: 40px;
      padding: 8px 24px;
      font-size: 14px;
      font-weight: 500;
      letter-spacing: 0.4px;
      text-transform: uppercase;
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: #FFFFFF;
    }

    /* Content area with comfortable spacing */
    .content {
      padding: 40px 36px 28px;
    }

    .greeting {
      font-size: 20px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 16px;
      letter-spacing: -0.3px;
    }

    /* Message box: clean, modern with accent left border and soft background */
    .message-box {
      background-color: #F9FAFB;
      border-left: 5px solid {{ setting('theme_color', '#4F46E5') }};
      padding: 20px 22px;
      border-radius: 0 14px 14px 0;
      margin-bottom: 32px;
      font-size: 15px;
      color: #374151;
      line-height: 1.7;
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.02);
    }

    /* Details table with minimalist style */
    .details-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
      background-color: #FFFFFF;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0,0,0,0.02);
    }

    .details-table th {
      font-weight: 600;
      color: #6B7280;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      padding: 14px 18px;
      text-align: left;
      background-color: #F9FAFB;
      border-bottom: 1px solid #E5E7EB;
    }

    .details-table td {
      font-size: 15px;
      font-weight: 500;
      color: #1F2937;
      padding: 14px 18px;
      border-bottom: 1px solid #F3F4F6;
      background-color: #FFFFFF;
    }

    .details-table tr:last-child td {
      border-bottom: none;
    }

    /* Total amount section – sophisticated gradient card */
    .total-box {
      background: #F9FAFB;
      border: 1px solid #E5E7EB;
      border-radius: 20px;
      padding: 22px 24px;
      text-align: center;
      margin-bottom: 32px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }

    .total-label {
      font-size: 13px;
      font-weight: 600;
      color: #6B7280;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      display: block;
      margin-bottom: 6px;
    }

    .total-amount {
      font-size: 34px;
      font-weight: 700;
      color: #0F172A;
      letter-spacing: -0.8px;
      line-height: 1.2;
      word-break: break-word;
    }

    /* PDF notification - clean card with icon */
    .attachment-notice {
      background: #FFFFFF;
      border: 1px solid #E5E7EB;
      border-radius: 16px;
      padding: 22px 20px;
      margin: 28px 0 18px;
      text-align: center;
      background: #FAFAFA;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .pdf-icon {
      font-size: 28px;
      margin-bottom: 8px;
      color: {{ setting('theme_color', '#4F46E5') }};
    }

    .attachment-title {
      font-size: 16px;
      font-weight: 600;
      color: #1F2937;
      margin-bottom: 6px;
    }

    .attachment-sub {
      font-size: 13px;
      color: #6B7280;
      line-height: 1.5;
      max-width: 280px;
      margin: 0 auto;
    }

    /* Footer with subtle top border and professional info */
    .footer {
      background-color: #F9FAFB;
      padding: 30px 36px 32px;
      text-align: center;
      border-top: 1px solid #EDF2F7;
      color: #4B5563;
      font-size: 14px;
    }

    .footer-company {
      font-weight: 700;
      font-size: 16px;
      color: #1F2937;
      margin-bottom: 8px;
      letter-spacing: -0.2px;
    }

    .footer-address {
      margin: 0 0 8px;
      font-size: 13px;
      color: #6B7280;
      line-height: 1.7;
    }

    .footer-contact {
      margin: 4px 0 0;
      font-size: 13px;
      color: #6B7280;
    }

    .footer-divider {
      width: 50px;
      height: 2px;
      background: #D1D5DB;
      margin: 12px auto 16px;
      border-radius: 2px;
    }

    /* Responsive adjustments */
    @media only screen and (max-width: 600px) {
      .email-wrapper {
        padding: 20px 10px;
      }
      .header {
        padding: 36px 20px 32px;
      }
      .content {
        padding: 30px 22px 20px;
      }
      .footer {
        padding: 24px 22px 28px;
      }
      .company-name {
        font-size: 22px;
      }
      .total-amount {
        font-size: 28px;
      }
      .details-table th,
      .details-table td {
        padding: 12px 12px;
      }
    }

    /* Dark mode support for email clients that respect prefers-color-scheme (progressive enhancement) */
    @media (prefers-color-scheme: dark) {
      /* Keep light design as email clients often override; but ensure readability */
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    <div class="email-container">
      
      <!-- Header Section -->
      <div class="header">
        <div class="header-content">
          <h1 class="company-name">{{ setting('company_name', config('app.name')) }}</h1>
          <div class="document-badge">Quotation</div>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="content">
        <div class="greeting">Hello,</div>
        
        <!-- Personalized message with clean design -->
        <div class="message-box">
          {!! nl2br(e($body)) !!}
        </div>
        
        <!-- Quotation Details Table -->
        <table class="details-table" role="presentation">
          <tr>
            <th>Quotation No.</th>
            <td>{{ $quotation->quotation_number }}</td>
          </tr>
          <tr>
            <th>Date Issued</th>
            <td>{{ $quotation->date->format('d M Y') }}</td>
          </tr>
          @if($quotation->valid_until)
          <tr>
            <th>Valid Until</th>
            <td>{{ $quotation->valid_until->format('d M Y') }}</td>
          </tr>
          @endif
          @if($quotation->subject)
          <tr>
            <th>Subject</th>
            <td>{{ $quotation->subject }}</td>
          </tr>
          @endif
        </table>
        
        <!-- Total Amount -->
        <div class="total-box">
          <span class="total-label">Total Amount</span>
          <span class="total-amount">{{ setting('currency_symbol', '₹') }}{{ number_format($quotation->total, 2) }}</span>
        </div>
        
        <!-- Attachment notice - professional and clear -->
        <div class="attachment-notice">
          <div class="pdf-icon">📄</div>
          <div class="attachment-title">Detailed quotation PDF attached</div>
          <div class="attachment-sub">
            Please review the attached document for a complete breakdown, itemized pricing, and terms.
          </div>
        </div>
      </div>
      
      <!-- Footer with company details -->
      <div class="footer">
        <div class="footer-company">{{ setting('company_name', config('app.name')) }}</div>
        @if(setting('company_address'))
        <div class="footer-address">{{ setting('company_address') }}</div>
        @endif
        <div class="footer-divider"></div>
        <div class="footer-contact">
          @if(setting('company_phone'))
            <span>📞 {{ setting('company_phone') }}</span>
          @endif
          @if(setting('company_phone') && setting('company_email'))
            <span style="margin: 0 8px;">•</span>
          @endif
          @if(setting('company_email'))
            <span>✉️ {{ setting('company_email') }}</span>
          @endif
        </div>
        @if(!setting('company_phone') && !setting('company_email'))
        <div class="footer-contact" style="color:#9CA3AF;">—</div>
        @endif
      </div>
      
    </div>
  </div>
</body>
</html>