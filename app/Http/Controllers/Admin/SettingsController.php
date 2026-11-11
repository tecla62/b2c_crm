<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    private array $imageKeys = [
        'company_logo', 'company_favicon', 'pdf_invoice_logo',
        'pdf_estimation_logo', 'pdf_watermark_image', 'pdf_signature',
        'pdf_company_seal', 'sidebar_logo', 'login_logo', 'login_bg_image',
    ];

    // ── Show settings page ─────────────────────────────────────────────────
    public function index(string $group = 'general')
    {
        $validGroups = ['general', 'invoice', 'estimation', 'quotation-email', 'pdf', 'mail', 'theme', 'login', 'notification', 'security', 'backup'];
        if (!in_array($group, $validGroups)) $group = 'general';

        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings', 'group'));
    }

    // ── Save a settings group (AJAX) ───────────────────────────────────────
    public function save(Request $request, string $group)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            // Handle image uploads
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $path = $file->store("settings/{$group}", 'public');
                // Delete old file
                $old = Setting::get($key);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                Setting::set($key, $path);
                continue;
            }

            // Handle boolean toggles (unchecked = not sent)
            Setting::set($key, $value ?? '');
        }

        // Handle boolean fields that were NOT submitted (unchecked checkboxes)
        $booleanKeys = Setting::where('group', $group)->where('type', 'boolean')->pluck('key');
        foreach ($booleanKeys as $bKey) {
            if (!array_key_exists($bKey, $data) && !$request->hasFile($bKey)) {
                Setting::set($bKey, '0');
            }
        }

        Setting::clearCache();

        return response()->json(['success' => true, 'message' => ucfirst($group) . ' settings saved successfully.']);
    }

    // ── Delete an image setting ────────────────────────────────────────────
    public function deleteImage(Request $request)
    {
        $key = $request->key;
        $val = Setting::get($key);
        if ($val && Storage::disk('public')->exists($val)) {
            Storage::disk('public')->delete($val);
        }
        Setting::set($key, '');
        return response()->json(['success' => true]);
    }

    // ── Send test email ────────────────────────────────────────────────────
    public function testMail(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);

        try {
            // Apply SMTP settings dynamically
            Config::set('mail.mailers.smtp.host',       Setting::get('mail_host'));
            Config::set('mail.mailers.smtp.port',       Setting::get('mail_port'));
            Config::set('mail.mailers.smtp.username',   Setting::get('mail_username'));
            Config::set('mail.mailers.smtp.password',   Setting::get('mail_password'));
            Config::set('mail.mailers.smtp.encryption', Setting::get('mail_encryption'));
            Config::set('mail.from.address',            Setting::get('mail_from_address'));
            Config::set('mail.from.name',               Setting::get('mail_from_name'));

            Mail::raw('This is a test email from ' . Setting::get('company_name', 'WaterProof CRM') . '. Your SMTP settings are working correctly!', function ($msg) use ($request) {
                $msg->to($request->test_email)
                    ->subject('Test Email — ' . Setting::get('company_name', 'WaterProof CRM'));
            });

            return response()->json(['success' => true, 'message' => 'Test email sent successfully to ' . $request->test_email]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 422);
        }
    }

    // ── Send test quotation email ───────────────────────────────────────────
    public function testQuotationEmail(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'test_email'   => 'required|email',
        ]);

        try {
            // Apply SMTP settings dynamically
            $this->applySmtpSettings();

            $quotation = \App\Models\Quotation::with(['customer', 'items'])->findOrFail($request->quotation_id);

            // Build subject and body from settings
            $subject = $this->parseTemplate(setting('quotation_email_subject', 'Quotation {{quotation_number}} from {{company_name}}'), $quotation);
            $body    = $this->parseTemplate(setting('quotation_email_body', "Dear {{customer_name}},\n\nPlease find your Quotation {{quotation_number}} attached.\n\nTotal Amount: {{total}}\n\nKindly review and revert.\n\nRegards,\n{{company_name}}"), $quotation);

            $showDesc = setting('quotation_email_include_desc', '1') == '1';

            Mail::to($request->test_email)->send(new \App\Mail\QuotationAutoMail(
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

            return response()->json(['success' => true, 'message' => 'Test quotation email sent successfully to ' . $request->test_email]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 422);
        }
    }

    // ── Apply SMTP settings from database ───────────────────────────────────
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

    // ── Parse template placeholders ─────────────────────────────────────────
    private function parseTemplate(string $template, \App\Models\Quotation $quotation): string
    {
        $customer  = $quotation->customer;
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

    public function testNotification(Request $request)
    {
        $user = $request->user();

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'crm.test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => 'Test notification',
                'message' => 'Your in-app notification center is working.',
                'module' => 'Settings',
                'action' => 'TEST',
                'icon' => 'bi-bell',
                'url' => route('admin.settings.group', 'notification', false),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent. Open the bell in the top header.',
        ]);
    }

    // ── Manual database backup ─────────────────────────────────────────────
    public function backup()
    {
        try {
            $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
            $path     = storage_path('app/backups/' . $filename);

            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            $db   = config('database.connections.mysql.database');
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $cmd = "mysqldump --user={$user} --password={$pass} --host={$host} {$db} > \"{$path}\"";
            exec($cmd, $output, $code);

            if ($code === 0 && file_exists($path)) {
                return response()->download($path, $filename)->deleteFileAfterSend(true);
            }

            return response()->json(['success' => false, 'message' => 'Backup failed. Ensure mysqldump is available.'], 500);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Clear application cache ────────────────────────────────────────────
    public function clearCache()
    {
        Setting::clearCache();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return response()->json(['success' => true, 'message' => 'Cache cleared successfully.']);
    }
}
