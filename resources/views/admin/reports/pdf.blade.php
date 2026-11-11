@php
    $primaryColor = setting('pdf_header_color', setting('theme_color', '#4f46e5'));
    $companyName = setting('company_name', config('app.name'));
    $companyEmail = setting('company_email', '');
    $companyPhone = setting('company_mobile', '');
    $companyAddress = implode(', ', array_filter([
        setting('company_address', ''),
        setting('company_city', ''),
        setting('company_state', ''),
        setting('company_pincode', ''),
    ]));
    if (!function_exists('report_pdf_base64_img')) {
        function report_pdf_base64_img(string $key): string {
            $val = \App\Models\Setting::get($key);
            if (!$val) {
                return '';
            }

            $path = public_path('storage/' . $val);
            if (!file_exists($path)) {
                return '';
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
                $image = @imagecreatefromwebp($path);
                if ($image) {
                    ob_start();
                    imagepng($image);
                    imagedestroy($image);
                    return 'data:image/png;base64,' . base64_encode(ob_get_clean());
                }
            }

            $mime = mime_content_type($path) ?: 'image/' . $extension;
            $data = file_get_contents($path);

            return 'data:' . $mime . ';base64,' . base64_encode($data);
        }
    }

    if (!function_exists('report_pdf_text')) {
        function report_pdf_text(mixed $value): string {
            $text = (string) $value;
            return str_replace(["\u{20B9}", 'â‚¹'], 'Rs. ', $text);
        }
    }

    $logoUrl = report_pdf_base64_img('pdf_invoice_logo') ?: report_pdf_base64_img('company_logo');
    $watermarkUrl = report_pdf_base64_img('pdf_watermark_image');
    $watermarkOpacity = ((int) setting('pdf_watermark_opacity', 15)) / 100;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $report['title'] }}</title>
<style>
*{box-sizing:border-box;}
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:9px;color:#1f2937;margin:0;background:#fff;}
.watermark{position:fixed;left:0;right:0;top:150px;text-align:center;z-index:-1;opacity:{{ $watermarkOpacity }};}
.watermark img{width:420px;max-height:420px;object-fit:contain;}
.header{background:{{ $primaryColor }};color:#fff;padding:16px 22px;display:table;width:100%;}
.header-left{display:table-cell;vertical-align:middle;width:70%;}
.header-right{display:table-cell;vertical-align:middle;text-align:right;width:30%;}
.logo{max-height:58px;max-width:180px;object-fit:contain;background:#fff;padding:6px;border-radius:6px;}
.brand{font-size:20px;font-weight:800;margin-bottom:4px;}
.meta{font-size:10px;opacity:.9;}
.wrap{padding:18px 22px;}
.title-row{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;}
.title{font-size:18px;font-weight:800;color:#111827;}
.sub{font-size:10px;color:#6b7280;margin-top:3px;}
.summary{width:100%;border-collapse:collapse;margin-bottom:16px;}
.summary td{width:25%;border:1px solid #e5e7eb;padding:10px;background:#f9fafb;}
.summary-label{font-size:9px;text-transform:uppercase;color:#6b7280;font-weight:700;}
.summary-value{font-size:13px;color:#111827;font-weight:800;margin-top:4px;}
.data{width:100%;border-collapse:collapse;}
.data th{background:#f3f4f6;color:#374151;text-transform:uppercase;font-size:7px;letter-spacing:.2px;border:1px solid #e5e7eb;padding:5px;text-align:left;}
.data td{border:1px solid #e5e7eb;padding:5px;vertical-align:top;word-break:break-word;}
.footer{position:fixed;left:22px;right:22px;bottom:14px;color:#9ca3af;font-size:9px;border-top:1px solid #e5e7eb;padding-top:8px;}
</style>
</head>
<body>
@if($watermarkUrl)
<div class="watermark">
    <img src="{{ $watermarkUrl }}" alt="">
</div>
@endif

<div class="header">
    <div class="header-left">
        <div class="brand">{{ $companyName }}</div>
        <div class="meta">{{ implode(' | ', array_filter([$companyAddress, $companyPhone, $companyEmail])) }}</div>
    </div>
    <div class="header-right">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="logo" alt="">
        @endif
    </div>
</div>

<div class="wrap">
    <div class="title-row">
        <div>
            <div class="title">{{ $report['title'] }}</div>
            <div class="sub">Generated {{ now()->format('d M Y, h:i A') }}</div>
        </div>
        <div class="sub">
            Period: {{ $filters['from'] ?: 'Start' }} to {{ $filters['to'] ?: 'Today' }}<br>
            Report Type: {{ $reportTypes[$filters['type']] ?? $filters['type'] }}
        </div>
    </div>

    <table class="summary">
        <tr>
            @foreach($report['summary'] as $label => $value)
                <td>
                    <div class="summary-label">{{ $label }}</div>
                    <div class="summary-value">{{ report_pdf_text($value) }}</div>
                </td>
                @if($loop->iteration % 4 === 0 && !$loop->last)
                    </tr><tr>
                @endif
            @endforeach
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach(($report['exportHeaders'] ?? $report['headers']) as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $row)
                <tr>
                    @foreach(($row['export'] ?? $row['view']) as $value)
                        <td>{{ report_pdf_text($value) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($report['headers']) }}">No data found for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="footer">
    {{ setting('pdf_footer_text', 'This is a computer generated document.') }}
</div>
</body>
</html>
