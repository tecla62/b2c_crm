<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General / Company ──────────────────────────────────────────
            ['key' => 'company_name',        'value' => 'WaterProof CRM',          'type' => 'text',     'group' => 'general', 'label' => 'Company Name'],
            ['key' => 'company_tagline',     'value' => 'Enterprise Management',   'type' => 'text',     'group' => 'general', 'label' => 'Company Tagline'],
            ['key' => 'company_owner_name',  'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Owner / Proprietor Name'],
            ['key' => 'company_description', 'value' => '',                        'type' => 'textarea', 'group' => 'general', 'label' => 'Company Description'],
            ['key' => 'company_logo',        'value' => '',                        'type' => 'image',    'group' => 'general', 'label' => 'Company Logo'],
            ['key' => 'company_favicon',     'value' => '',                        'type' => 'image',    'group' => 'general', 'label' => 'Company Favicon'],
            ['key' => 'company_address',     'value' => '',                        'type' => 'textarea', 'group' => 'general', 'label' => 'Address'],
            ['key' => 'company_city',        'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'City'],
            ['key' => 'company_state',       'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'State'],
            ['key' => 'company_country',     'value' => 'India',                   'type' => 'text',     'group' => 'general', 'label' => 'Country'],
            ['key' => 'company_pincode',     'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Pincode'],
            ['key' => 'company_mobile',      'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Mobile Number'],
            ['key' => 'company_alt_mobile',  'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Alternate Number'],
            ['key' => 'company_email',       'value' => '',                        'type' => 'email',    'group' => 'general', 'label' => 'Email Address'],
            ['key' => 'company_website',     'value' => '',                        'type' => 'url',      'group' => 'general', 'label' => 'Website URL'],
            ['key' => 'company_gst',         'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'GST Number'],
            ['key' => 'company_pan',         'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'PAN Number'],
            ['key' => 'timezone',            'value' => 'Asia/Kolkata',            'type' => 'select',   'group' => 'general', 'label' => 'Timezone'],
            ['key' => 'date_format',         'value' => 'd/m/Y',                   'type' => 'select',   'group' => 'general', 'label' => 'Date Format'],
            ['key' => 'whatsapp_number',     'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'WhatsApp Number'],
            ['key' => 'social_facebook',     'value' => '',                        'type' => 'url',      'group' => 'general', 'label' => 'Facebook URL'],
            ['key' => 'social_instagram',    'value' => '',                        'type' => 'url',      'group' => 'general', 'label' => 'Instagram URL'],
            ['key' => 'social_twitter',      'value' => '',                        'type' => 'url',      'group' => 'general', 'label' => 'Twitter URL'],
            ['key' => 'social_linkedin',     'value' => '',                        'type' => 'url',      'group' => 'general', 'label' => 'LinkedIn URL'],
            ['key' => 'social_youtube',      'value' => '',                        'type' => 'url',      'group' => 'general', 'label' => 'YouTube URL'],

            // ── Tax & Legal ────────────────────────────────────────────────
            ['key' => 'company_gst_type',    'value' => 'regular',                 'type' => 'select',   'group' => 'general', 'label' => 'GST Registration Type'],
            ['key' => 'company_cin',         'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'CIN Number'],
            ['key' => 'company_msme',        'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'MSME / Udyam Number'],

            // ── Bank Details ───────────────────────────────────────────────
            ['key' => 'bank_name',           'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Bank Name'],
            ['key' => 'bank_account_name',   'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Account Holder Name'],
            ['key' => 'bank_account_number', 'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Account Number'],
            ['key' => 'bank_ifsc',           'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'IFSC Code'],
            ['key' => 'bank_branch',         'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Branch Name'],
            ['key' => 'bank_account_type',   'value' => 'current',                 'type' => 'select',   'group' => 'general', 'label' => 'Account Type'],
            ['key' => 'bank_swift',          'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'SWIFT / BIC Code'],
            ['key' => 'bank_upi_id',         'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'UPI ID'],
            ['key' => 'bank_qr_code',        'value' => '',                        'type' => 'image',    'group' => 'general', 'label' => 'UPI QR Code Image'],
            ['key' => 'bank_2_name',         'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Bank 2 — Bank Name'],
            ['key' => 'bank_2_account_name', 'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Bank 2 — Account Holder'],
            ['key' => 'bank_2_account_number','value' => '',                       'type' => 'text',     'group' => 'general', 'label' => 'Bank 2 — Account Number'],
            ['key' => 'bank_2_ifsc',         'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Bank 2 — IFSC Code'],
            ['key' => 'bank_2_branch',       'value' => '',                        'type' => 'text',     'group' => 'general', 'label' => 'Bank 2 — Branch'],

            // ── Invoice Settings ───────────────────────────────────────────
            ['key' => 'invoice_prefix',      'value' => 'INV',                     'type' => 'text',     'group' => 'invoice', 'label' => 'Invoice Prefix'],
            ['key' => 'invoice_start_no',    'value' => '1',                       'type' => 'number',   'group' => 'invoice', 'label' => 'Starting Invoice Number'],
            ['key' => 'invoice_footer_notes','value' => 'Thank you for your business!', 'type' => 'textarea', 'group' => 'invoice', 'label' => 'Invoice Footer Notes'],
            ['key' => 'invoice_due_days',    'value' => '15',                      'type' => 'number',   'group' => 'invoice', 'label' => 'Due Days'],
            ['key' => 'default_tax',         'value' => '18',                      'type' => 'number',   'group' => 'invoice', 'label' => 'Default Tax %'],
            ['key' => 'currency_symbol',     'value' => '₹',                       'type' => 'text',     'group' => 'invoice', 'label' => 'Currency Symbol'],
            ['key' => 'currency_position',   'value' => 'before',                  'type' => 'select',   'group' => 'invoice', 'label' => 'Currency Position'],
            ['key' => 'decimal_precision',   'value' => '2',                       'type' => 'number',   'group' => 'invoice', 'label' => 'Decimal Precision'],

            // ── Estimation Settings ────────────────────────────────────────
            ['key' => 'estimation_prefix',      'value' => 'EST',  'type' => 'text',     'group' => 'estimation', 'label' => 'Estimation Prefix'],
            ['key' => 'estimation_start_no',    'value' => '1',    'type' => 'number',   'group' => 'estimation', 'label' => 'Starting Estimate Number'],
            ['key' => 'estimation_notes',       'value' => '',     'type' => 'textarea', 'group' => 'estimation', 'label' => 'Default Notes'],
            ['key' => 'estimation_terms',       'value' => '',     'type' => 'textarea', 'group' => 'estimation', 'label' => 'Default Terms'],
            ['key' => 'estimation_show_gst',    'value' => '1',    'type' => 'boolean',  'group' => 'estimation', 'label' => 'Show GST Option'],
            ['key' => 'estimation_expiry_days', 'value' => '30',   'type' => 'number',   'group' => 'estimation', 'label' => 'Auto Expiry Days'],
            ['key' => 'quotation_guarantee',    'value' => '',     'type' => 'textarea', 'group' => 'estimation', 'label' => 'Default Guarantee'],
            ['key' => 'quotation_terms',        'value' => '',     'type' => 'textarea', 'group' => 'estimation', 'label' => 'Default Terms & Conditions'],
            ['key' => 'quotation_payment',      'value' => '',     'type' => 'textarea', 'group' => 'estimation', 'label' => 'Default Payment Terms'],

            // ── PDF & Document Settings ────────────────────────────────────
            ['key' => 'pdf_invoice_logo',    'value' => '',                        'type' => 'image',    'group' => 'pdf', 'label' => 'Invoice Logo'],
            ['key' => 'pdf_estimation_logo', 'value' => '',                        'type' => 'image',    'group' => 'pdf', 'label' => 'Estimation Logo'],
            ['key' => 'pdf_watermark_image', 'value' => '',                        'type' => 'image',    'group' => 'pdf', 'label' => 'Watermark Image'],
            ['key' => 'pdf_watermark_text',  'value' => 'CONFIDENTIAL',            'type' => 'text',     'group' => 'pdf', 'label' => 'Watermark Text'],
            ['key' => 'pdf_watermark_position','value' => 'center',               'type' => 'select',   'group' => 'pdf', 'label' => 'Watermark Position'],
            ['key' => 'pdf_watermark_opacity','value' => '20',                    'type' => 'number',   'group' => 'pdf', 'label' => 'Watermark Opacity (%)'],
            ['key' => 'pdf_footer_text',     'value' => 'This is a computer generated document.', 'type' => 'textarea', 'group' => 'pdf', 'label' => 'PDF Footer Text'],
            ['key' => 'pdf_signature',       'value' => '',                        'type' => 'image',    'group' => 'pdf', 'label' => 'Authorized Signature'],
            ['key' => 'pdf_company_seal',    'value' => '',                        'type' => 'image',    'group' => 'pdf', 'label' => 'Company Seal / Stamp'],
            ['key' => 'pdf_terms',           'value' => '',                        'type' => 'textarea', 'group' => 'pdf', 'label' => 'Terms & Conditions'],
            ['key' => 'pdf_header_color',    'value' => '#4f46e5',                 'type' => 'color',    'group' => 'pdf', 'label' => 'PDF Header Color'],
            ['key' => 'pdf_footer_color',    'value' => '#1e293b',                 'type' => 'color',    'group' => 'pdf', 'label' => 'PDF Footer Color'],

            // ── Mail / SMTP Settings ───────────────────────────────────────
            ['key' => 'mail_host',           'value' => 'smtp.mailtrap.io',        'type' => 'text',     'group' => 'mail', 'label' => 'Mail Host'],
            ['key' => 'mail_port',           'value' => '587',                     'type' => 'number',   'group' => 'mail', 'label' => 'Mail Port'],
            ['key' => 'mail_username',       'value' => '',                        'type' => 'text',     'group' => 'mail', 'label' => 'Mail Username'],
            ['key' => 'mail_password',       'value' => '',                        'type' => 'password', 'group' => 'mail', 'label' => 'Mail Password'],
            ['key' => 'mail_encryption',     'value' => 'tls',                     'type' => 'select',   'group' => 'mail', 'label' => 'Encryption Type'],
            ['key' => 'mail_from_address',   'value' => '',                        'type' => 'email',    'group' => 'mail', 'label' => 'From Email'],
            ['key' => 'mail_from_name',      'value' => 'WaterProof CRM',          'type' => 'text',     'group' => 'mail', 'label' => 'From Name'],

            // ── Theme & UI Settings ────────────────────────────────────────
            ['key' => 'theme_color',         'value' => '#4f46e5',                 'type' => 'color',    'group' => 'theme', 'label' => 'Theme Color'],
            ['key' => 'sidebar_color',       'value' => '#0f172a',                 'type' => 'color',    'group' => 'theme', 'label' => 'Sidebar Color'],
            ['key' => 'header_color',        'value' => '#ffffff',                 'type' => 'color',    'group' => 'theme', 'label' => 'Header Color'],
            ['key' => 'dark_mode_default',   'value' => '0',                       'type' => 'boolean',  'group' => 'theme', 'label' => 'Dark Mode Default'],
            ['key' => 'compact_sidebar',     'value' => '0',                       'type' => 'boolean',  'group' => 'theme', 'label' => 'Compact Sidebar'],
            ['key' => 'font_size',           'value' => 'medium',                  'type' => 'select',   'group' => 'theme', 'label' => 'Font Size'],
            ['key' => 'sidebar_logo',        'value' => '',                        'type' => 'image',    'group' => 'theme', 'label' => 'Sidebar Logo'],

            // ── Login Page Settings ────────────────────────────────────────
            ['key' => 'login_logo',          'value' => '',                        'type' => 'image',    'group' => 'login', 'label' => 'Login Logo'],
            ['key' => 'login_bg_image',      'value' => '',                        'type' => 'image',    'group' => 'login', 'label' => 'Login Background Image'],
            ['key' => 'login_welcome_text',  'value' => 'Welcome Back!',           'type' => 'text',     'group' => 'login', 'label' => 'Welcome Text'],
            ['key' => 'login_page_title',    'value' => 'Sign in to your account', 'type' => 'text',     'group' => 'login', 'label' => 'Login Page Title'],
            ['key' => 'login_footer_text',   'value' => '© 2025 WaterProof CRM. All rights reserved.', 'type' => 'text', 'group' => 'login', 'label' => 'Login Footer Text'],

            // ── Notification Settings ──────────────────────────────────────
            ['key' => 'notif_email',         'value' => '1',                       'type' => 'boolean',  'group' => 'notification', 'label' => 'Email Notifications'],
            ['key' => 'notif_sms',           'value' => '0',                       'type' => 'boolean',  'group' => 'notification', 'label' => 'SMS Notifications'],
            ['key' => 'notif_whatsapp',      'value' => '0',                       'type' => 'boolean',  'group' => 'notification', 'label' => 'WhatsApp Notifications'],
            ['key' => 'notif_browser',       'value' => '1',                       'type' => 'boolean',  'group' => 'notification', 'label' => 'Browser Notifications'],

            // ── Security Settings ──────────────────────────────────────────
            ['key' => 'session_timeout',     'value' => '120',                     'type' => 'number',   'group' => 'security', 'label' => 'Session Timeout (minutes)'],
            ['key' => 'login_attempt_limit', 'value' => '5',                       'type' => 'number',   'group' => 'security', 'label' => 'Login Attempt Limit'],
            ['key' => 'password_min_length', 'value' => '8',                       'type' => 'number',   'group' => 'security', 'label' => 'Min Password Length'],
            ['key' => 'password_require_upper','value' => '1',                     'type' => 'boolean',  'group' => 'security', 'label' => 'Require Uppercase'],
            ['key' => 'password_require_number','value' => '1',                    'type' => 'boolean',  'group' => 'security', 'label' => 'Require Number'],
            ['key' => 'password_require_symbol','value' => '0',                    'type' => 'boolean',  'group' => 'security', 'label' => 'Require Symbol'],
            ['key' => 'recaptcha_enabled',   'value' => '0',                       'type' => 'boolean',  'group' => 'security', 'label' => 'Google reCAPTCHA'],
            ['key' => 'recaptcha_site_key',  'value' => '',                        'type' => 'text',     'group' => 'security', 'label' => 'reCAPTCHA Site Key'],
            ['key' => 'recaptcha_secret_key','value' => '',                        'type' => 'password', 'group' => 'security', 'label' => 'reCAPTCHA Secret Key'],
            ['key' => 'two_factor_default',  'value' => '0',                       'type' => 'boolean',  'group' => 'security', 'label' => 'Two Factor Authentication'],

            // ── Backup Settings ────────────────────────────────────────────
            ['key' => 'auto_backup',         'value' => '0',                       'type' => 'boolean',  'group' => 'backup', 'label' => 'Auto Backup'],
            ['key' => 'backup_frequency',    'value' => 'daily',                   'type' => 'select',   'group' => 'backup', 'label' => 'Backup Frequency'],
            ['key' => 'backup_retention',    'value' => '7',                       'type' => 'number',   'group' => 'backup', 'label' => 'Retention Days'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
