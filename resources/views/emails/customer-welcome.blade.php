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
<title>Welcome — {{ $companyName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;padding:40px 16px;">
<tr><td align="center">

  <table width="600" cellpadding="0" cellspacing="0" border="0"
         style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
      <td style="background:{{ $primaryColor }};padding:0;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center" style="padding:40px 40px 32px;">
              @if($logoUrl)
              <img src="{{ $logoUrl }}" alt="{{ $companyName }}"
                   style="height:56px;max-width:200px;object-fit:contain;display:block;margin:0 auto 20px;">
              @else
              <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:12px;padding:12px 24px;margin-bottom:20px;">
                <span style="color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{{ $companyName }}</span>
              </div>
              @endif
              <h1 style="color:#ffffff;font-size:24px;font-weight:700;margin:0 0 8px;line-height:1.3;">
                Welcome, {{ $customer->name }}! 🎉
              </h1>
              <p style="color:rgba(255,255,255,0.85);font-size:15px;margin:0;line-height:1.5;">
                We're thrilled to have you with us
              </p>
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

        <p style="font-size:16px;font-weight:600;color:#1e293b;margin:0 0 8px;">
          Hello, {{ $customer->name }}! 👋
        </p>
        <p style="font-size:14px;color:#64748b;line-height:1.8;margin:0 0 28px;">
          Thank you for choosing <strong style="color:#1e293b;">{{ $companyName }}</strong>.
          We're excited to work with you and are committed to providing you with the best service possible.
          Your profile has been successfully created in our system.
        </p>

        <!-- Profile Details -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
               style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
          <tr>
            <td style="background:{{ $primaryColor }};padding:12px 20px;">
              <span style="color:#ffffff;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">
                📋 Your Profile Details
              </span>
            </td>
          </tr>
          @php
            $rows = array_values(array_filter([
              ['label' => 'Name',    'value' => $customer->name],
              ['label' => 'Email',   'value' => $customer->email],
              ['label' => 'Mobile',  'value' => $customer->mobile],
              ['label' => 'Company', 'value' => $customer->company_name],
              ['label' => 'City',    'value' => $customer->city],
            ], fn($r) => !empty($r['value'])));
          @endphp
          @foreach($rows as $i => $row)
          <tr>
            <td style="padding:12px 20px;{{ $i % 2 !== 0 ? 'background:#ffffff;' : '' }}{{ $i < count($rows)-1 ? 'border-bottom:1px solid #e2e8f0;' : '' }}">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;width:35%;">{{ $row['label'] }}</td>
                  <td style="font-size:13px;font-weight:600;color:#1e293b;">{{ $row['value'] }}</td>
                </tr>
              </table>
            </td>
          </tr>
          @endforeach
        </table>

        <!-- What's Next -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
          <tr>
            <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px;">
              <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#166534;">✅ What happens next?</p>
              <p style="margin:0 0 6px;font-size:13px;color:#15803d;line-height:1.7;">&bull;&nbsp; Our team will reach out to you shortly</p>
              <p style="margin:0 0 6px;font-size:13px;color:#15803d;line-height:1.7;">&bull;&nbsp; Feel free to contact us anytime with questions</p>
              @if($customer->assignedTo)
              <p style="margin:0;font-size:13px;color:#15803d;line-height:1.7;">&bull;&nbsp; Your dedicated contact: <strong>{{ $customer->assignedTo->name }}</strong></p>
              @endif
            </td>
          </tr>
        </table>

        <!-- Contact Us -->
        @if($companyEmail || $companyPhone || $companyWebsite)
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
          <tr>
            <td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
              <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#1e293b;">📞 Contact Us</p>
              @if($companyEmail)
              <p style="margin:0 0 6px;font-size:13px;color:#64748b;">
                📧 <a href="mailto:{{ $companyEmail }}" style="color:#4f46e5;text-decoration:none;">{{ $companyEmail }}</a>
              </p>
              @endif
              @if($companyPhone)
              <p style="margin:0 0 6px;font-size:13px;color:#64748b;">📱 {{ $companyPhone }}</p>
              @endif
              @if($companyWebsite)
              <p style="margin:0;font-size:13px;color:#64748b;">
                🌐 <a href="{{ $companyWebsite }}" style="color:#4f46e5;text-decoration:none;">{{ $companyWebsite }}</a>
              </p>
              @endif
            </td>
          </tr>
        </table>
        @endif

        <p style="font-size:14px;color:#64748b;line-height:1.7;margin:0;">
          We look forward to serving you.<br>
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
              <p style="margin:0 0 6px;font-size:12px;color:#94a3b8;line-height:1.6;">
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
                © {{ date('Y') }} {{ $companyName }}. All rights reserved.<br>
                You received this email because you were registered as a customer.
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
