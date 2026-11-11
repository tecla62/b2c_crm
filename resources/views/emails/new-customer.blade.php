@php
    $companyName    = setting('company_name', config('app.name', 'CRM'));
    $companyEmail   = setting('company_email', '');
    $companyPhone   = setting('company_mobile', '');
    $companyWebsite = setting('company_website', '');
    $primaryColor   = setting('theme_color', '#4f46e5');

    $logoPath = \App\Models\Setting::get('company_logo');
    $logoUrl  = ($logoPath && file_exists(public_path('storage/' . $logoPath)))
                ? url('storage/' . $logoPath)
                : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>New Customer — {{ $companyName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;padding:40px 16px;">
<tr><td align="center">

  <table width="600" cellpadding="0" cellspacing="0" border="0"
         style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
      <td style="background:{{ $primaryColor }};padding:0;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center" style="padding:36px 40px 28px;">
              @if($logoUrl)
              <img src="{{ $logoUrl }}" alt="{{ $companyName }}"
                   style="height:50px;max-width:180px;object-fit:contain;display:block;margin:0 auto 18px;">
              @else
              <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:12px;padding:10px 22px;margin-bottom:18px;">
                <span style="color:#ffffff;font-size:20px;font-weight:800;">{{ $companyName }}</span>
              </div>
              @endif
              <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:50px;padding:6px 18px;margin-bottom:14px;">
                <span style="color:#ffffff;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">🎉 New Customer</span>
              </div>
              <h1 style="color:#ffffff;font-size:22px;font-weight:700;margin:0;line-height:1.3;">
                {{ $customer->name }} has been added!
              </h1>
            </td>
          </tr>
          <tr>
            <td style="padding:0;line-height:0;">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 40" style="display:block;width:100%;" preserveAspectRatio="none" height="40">
                <path d="M0,40 C150,0 450,0 600,40 L600,40 L0,40 Z" fill="#ffffff"/>
              </svg>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="padding:8px 40px 32px;">

        <p style="font-size:15px;font-weight:600;color:#1e293b;margin:0 0 6px;">Hello Team 👋</p>
        <p style="font-size:14px;color:#64748b;line-height:1.7;margin:0 0 28px;">
          A new customer has been added to <strong style="color:#1e293b;">{{ $companyName }}</strong> CRM.
          Here are the details:
        </p>

        <!-- Customer Details Box -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
               style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
          <tr>
            <td style="background:{{ $primaryColor }};padding:11px 20px;">
              <span style="color:#ffffff;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">
                👤 Customer Details
              </span>
            </td>
          </tr>

          @php
            $rows = [
              ['label' => 'Name',    'value' => $customer->name],
              ['label' => 'Email',   'value' => $customer->email   ?? '—'],
              ['label' => 'Mobile',  'value' => $customer->mobile  ?? '—'],
              ['label' => 'Type',    'value' => ucfirst($customer->type)],
              ['label' => 'Company', 'value' => $customer->company_name ?? '—'],
              ['label' => 'Source',  'value' => $customer->source ? ucfirst($customer->source) : '—'],
              ['label' => 'City',    'value' => $customer->city    ?? '—'],
              ['label' => 'Status',  'value' => ucfirst($customer->status)],
            ];
          @endphp

          @foreach($rows as $i => $row)
          <tr>
            <td style="padding:12px 20px;{{ $i % 2 === 0 ? 'background:#ffffff;' : '' }}border-bottom:1px solid #e2e8f0;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;width:35%;">
                    {{ $row['label'] }}
                  </td>
                  <td style="font-size:13px;font-weight:600;color:#1e293b;">
                    {{ $row['value'] }}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          @endforeach

          @if($customer->assignedTo)
          <tr>
            <td style="padding:12px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;width:35%;">
                    Assigned To
                  </td>
                  <td>
                    <span style="display:inline-block;background:#eef2ff;color:#4f46e5;font-size:12px;font-weight:700;padding:3px 12px;border-radius:20px;">
                      {{ $customer->assignedTo->name }}
                    </span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          @endif
        </table>

        <!-- CTA -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
          <tr>
            <td align="center">
              <a href="{{ url('/admin/customers/' . $customer->id) }}"
                 style="display:inline-block;background:{{ $primaryColor }};color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;padding:13px 36px;border-radius:10px;letter-spacing:0.3px;">
                View Customer →
              </a>
            </td>
          </tr>
        </table>

        <!-- Info box -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:13px 18px;">
              <p style="margin:0;font-size:13px;color:#166534;line-height:1.6;">
                ✅ <strong>Added on:</strong> {{ $customer->created_at->format('d M Y, h:i A') }}
                @if($customer->createdBy)
                  &nbsp;·&nbsp; <strong>By:</strong> {{ $customer->createdBy->name }}
                @endif
              </p>
            </td>
          </tr>
        </table>

        <p style="font-size:14px;color:#64748b;line-height:1.7;margin:0;">
          This is an automated notification from your CRM system.<br>
          <strong style="color:#1e293b;">— The {{ $companyName }} Team</strong>
        </p>

      </td>
    </tr>

    <!-- Divider -->
    <tr>
      <td style="padding:0 40px;">
        <hr style="border:none;border-top:1px solid #f1f5f9;margin:0;">
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="padding:22px 40px;background:#f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center">
              <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:#1e293b;">{{ $companyName }}</p>
              <p style="margin:0 0 6px;font-size:12px;color:#94a3b8;">
                @if($companyEmail)
                  <a href="mailto:{{ $companyEmail }}" style="color:#94a3b8;text-decoration:none;">{{ $companyEmail }}</a>
                @endif
                @if($companyEmail && $companyPhone) &nbsp;·&nbsp; @endif
                @if($companyPhone) {{ $companyPhone }} @endif
              </p>
              @if($companyWebsite)
              <p style="margin:0 0 10px;">
                <a href="{{ $companyWebsite }}" style="color:#4f46e5;font-size:12px;text-decoration:none;">{{ $companyWebsite }}</a>
              </p>
              @endif
              <p style="margin:0;font-size:11px;color:#cbd5e1;">
                © {{ date('Y') }} {{ $companyName }}. All rights reserved.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>

</td></tr>
</table>
</body>
</html>
