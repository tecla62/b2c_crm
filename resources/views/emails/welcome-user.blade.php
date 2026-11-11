@php
    $companyName    = setting('company_name', config('app.name', 'CRM'));
    $companyEmail   = setting('company_email', '');
    $companyPhone   = setting('company_mobile', '');
    $companyWebsite = setting('company_website', '');
    $primaryColor   = setting('theme_color', '#4f46e5');

    // Absolute URL for logo — required for email clients
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
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Welcome — {{ $companyName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;padding:40px 16px;">
<tr><td align="center">

  <!-- Wrapper -->
  <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

    <!-- ── Header ── -->
    <tr>
      <td style="background:{{ $primaryColor }};padding:0;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center" style="padding:40px 40px 32px;">
              @if($logoUrl)
              <img src="{{ $logoUrl }}" alt="{{ $companyName }}"
                   style="height:56px;max-width:200px;object-fit:contain;display:block;margin:0 auto 20px;"
                   width="200" height="56">
              @else
              <!-- Fallback text logo -->
              <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:12px;padding:12px 24px;margin-bottom:20px;">
                <span style="color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{{ $companyName }}</span>
              </div>
              @endif
              <h1 style="color:#ffffff;font-size:24px;font-weight:700;margin:0 0 8px;line-height:1.3;">
                Welcome to {{ $companyName }}! 🎉
              </h1>
              <p style="color:rgba(255,255,255,0.8);font-size:15px;margin:0;line-height:1.5;">
                Your account has been created successfully
              </p>
            </td>
          </tr>
          <!-- Decorative wave bottom -->
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

    <!-- ── Body ── -->
    <tr>
      <td style="padding:8px 40px 32px;">

        <!-- Greeting -->
        <p style="font-size:16px;font-weight:600;color:#1e293b;margin:0 0 8px;">
          Hello, {{ $user->name }}! 👋
        </p>
        <p style="font-size:14px;color:#64748b;line-height:1.7;margin:0 0 28px;">
          Your account on <strong style="color:#1e293b;">{{ $companyName }}</strong> is ready.
          Below are your login credentials. Please keep them confidential and
          <strong style="color:#dc2626;">change your password</strong> after your first login.
        </p>

        <!-- Credentials Box -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
               style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
          <tr>
            <td style="background:{{ $primaryColor }};padding:12px 20px;">
              <span style="color:#ffffff;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">
                @if($inviteUrl) 🔗 Set Your Password @else 🔐 Your Login Credentials @endif
              </span>
            </td>
          </tr>
          <!-- Login URL -->
          <tr>
            <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;width:40%;">Login URL</td>
                  <td style="font-size:13px;font-weight:600;color:#4f46e5;font-family:monospace;">
                    <a href="{{ url('/login') }}" style="color:#4f46e5;text-decoration:none;">{{ url('/login') }}</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- Email -->
          <tr>
            <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;background:#ffffff;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;width:40%;">Email</td>
                  <td style="font-size:13px;font-weight:600;color:#1e293b;font-family:monospace;">{{ $user->email }}</td>
                </tr>
              </table>
            </td>
          </tr>
          @if($plainPassword)
          <!-- Password -->
          <tr>
            <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;width:40%;">Password</td>
                  <td>
                    <span style="display:inline-block;background:#1e293b;color:#f1f5f9;font-family:monospace;font-size:14px;font-weight:700;padding:4px 14px;border-radius:6px;letter-spacing:1px;">
                      {{ $plainPassword }}
                    </span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          @endif
          @if($user->role_name)
          <!-- Role -->
          <tr>
            <td style="padding:14px 20px;background:#ffffff;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;width:40%;">Role</td>
                  <td>
                    <span style="display:inline-block;background:#eef2ff;color:#4f46e5;font-size:12px;font-weight:700;padding:3px 12px;border-radius:20px;">
                      {{ $user->role_name }}
                    </span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          @endif
        </table>

        <!-- CTA Button -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
          <tr>
            <td align="center">
              @if($inviteUrl)
              <a href="{{ $inviteUrl }}"
                 style="display:inline-block;background:#d97706;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 40px;border-radius:10px;letter-spacing:0.3px;">
                Set My Password →
              </a>
              <p style="margin:10px 0 0;font-size:12px;color:#94a3b8;">This link expires in 24 hours</p>
              @else
              <a href="{{ url('/login') }}"
                 style="display:inline-block;background:{{ $primaryColor }};color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 40px;border-radius:10px;letter-spacing:0.3px;">
                Login to Your Account →
              </a>
              @endif
            </td>
          </tr>
        </table>

        <!-- Warning -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 18px;">
              <p style="margin:0;font-size:13px;color:#9a3412;line-height:1.6;">
                ⚠️ <strong>Security Notice:</strong> Please change your password immediately after your first login.
                Never share your credentials with anyone.
              </p>
            </td>
          </tr>
        </table>

        <!-- Sign off -->
        <p style="font-size:14px;color:#64748b;line-height:1.7;margin:0;">
          If you have any questions, please contact your system administrator.<br>
          <strong style="color:#1e293b;">— The {{ $companyName }} Team</strong>
        </p>

      </td>
    </tr>

    <!-- ── Divider ── -->
    <tr>
      <td style="padding:0 40px;">
        <hr style="border:none;border-top:1px solid #f1f5f9;margin:0;">
      </td>
    </tr>

    <!-- ── Footer ── -->
    <tr>
      <td style="padding:24px 40px;background:#f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center">
              <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1e293b;">{{ $companyName }}</p>
              <p style="margin:0 0 6px;font-size:12px;color:#94a3b8;line-height:1.6;">
                @if($companyEmail)
                  <a href="mailto:{{ $companyEmail }}" style="color:#94a3b8;text-decoration:none;">{{ $companyEmail }}</a>
                @endif
                @if($companyEmail && $companyPhone) &nbsp;·&nbsp; @endif
                @if($companyPhone) {{ $companyPhone }} @endif
              </p>
              @if($companyWebsite)
              <p style="margin:0 0 12px;">
                <a href="{{ $companyWebsite }}" style="color:#4f46e5;font-size:12px;text-decoration:none;">{{ $companyWebsite }}</a>
              </p>
              @endif
              <p style="margin:0;font-size:11px;color:#cbd5e1;">
                © {{ date('Y') }} {{ $companyName }}. All rights reserved.<br>
                This email was sent because an account was created for you.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
  <!-- /Wrapper -->

</td></tr>
</table>

</body>
</html>
