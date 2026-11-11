# Professional Guarantee, Payment & Terms - Implementation Guide

## ✅ What Was Added

### 1. **Default Settings for Terms & Guarantee**
You can now set company-wide default values for:
- **Terms & Conditions** - Default terms applied to all estimations
- **Guarantee & Warranty** - Default guarantee information
- **Payment Terms** - Default payment instructions

### 2. **Smart Fallback System**
The system uses a smart priority:
1. **First**: Uses quotation-specific terms (if filled in)
2. **Second**: Falls back to company defaults from settings
3. **Final**: Shows nothing if neither is set

This means:
- 🎯 Each quotation can have custom terms
- 🏢 Reduces repetition - set defaults once
- 📋 Professional consistency across all PDFs

### 3. **Enhanced Professional Design**
- Smooth hover effects with subtle animations
- Color-coded sections (blue/green/amber)
- Better spacing and typography
- Icons for visual distinction
- Responsive layout (adjusts to 1, 2, or 3 columns)

---

## 🚀 How to Setup

### Step 1: Set Default Terms in Settings

1. Go to **Admin Panel** → **Settings** → **PDF & Document Settings**
2. Scroll to **"Default Estimation Terms"** section
3. Fill in three areas:

**Default Terms & Conditions:**
```
1. All prices are exclusive of taxes.
2. Validity: 30 days from quotation date.
3. This is a quotation only, not an invoice.
4. Changes to scope may affect pricing.
```

**Default Guarantee & Warranty:**
```
1 Year warranty on workmanship.
- Defects will be rectified at no additional cost.
- Warranty covers material defects only.
- Does not cover damage from misuse.
```

**Default Payment Terms:**
```
50% advance required to start work.
Balance due upon completion.
Payment via bank transfer, cheque, or cash.
Invoice will be issued after payment.
```

4. Click **"Save Changes"**

### Step 2: Test in Quotations

**Option A: Use Defaults**
- Create a quotation without filling Terms/Guarantee/Payment
- Print PDF → See defaults automatically appear

**Option B: Override Defaults**
- Create a quotation and fill in custom Terms/Guarantee/Payment
- Print PDF → See custom values (defaults ignored)
- Perfect for special cases

---

## 📊 What Appears in PDF

### Before (without defaults):
```
┌─────────────────────┐
│ Service Items List  │
├─────────────────────┤
│ Bank Details (only) │
├─────────────────────┤
│ Signature Block     │
└─────────────────────┘
```

### After (with defaults):
```
┌──────────────────────────────────┐
│ Service Items List               │
├──────────────────────────────────┤
│ 📋 Terms, Conditions & Guarantee │
│ ┌─────────────┬───────┬────────┐ │
│ │ Terms & C   │Guard. │Payment │ │
│ │(blue box)   │(green)│(amber) │ │
│ └─────────────┴───────┴────────┘ │
├──────────────────────────────────┤
│ Bank Details                     │
├──────────────────────────────────┤
│ Signature Block with Seal        │
└──────────────────────────────────┘
```

---

## 🎨 Design Features

### Professional Styling:
✅ Color-coded headers:
- **Terms**: Indigo/Blue (#eef2ff background)
- **Guarantee**: Green (#f0fdf4 background)
- **Payment**: Amber (#fffbeb background)

✅ Interactive hover effects:
- Subtle shadow on hover
- Color shift on border
- Smooth transitions (0.3s)

✅ Responsive layout:
- 3 columns when all three sections present
- 2 columns when two sections present
- Full width (1 column) when one section present

### Typography:
- Uppercase headers for prominence
- 12.5px body text for readability
- 1.8 line height for comfortable reading
- Professional icons (📋 🛡️ 💳)

---

## 💡 Best Practices

### Writing Effective Terms:

**DO:**
```
✓ Keep points short and clear
✓ Use bullet points
✓ 3-5 main points maximum
✓ Use simple, professional language
✓ Be specific about dates/amounts
```

**DON'T:**
```
✗ Don't write paragraphs
✗ Don't use legal jargon
✗ Don't exceed 6 points
✗ Don't use ALL CAPS
✗ Don't include special characters
```

### Example Terms:

**Basic (for standard work):**
```
1. Prices quoted exclude GST/taxes.
2. Valid for 30 days from date.
3. 50% advance, balance on completion.
4. Work begins after advance is received.
5. Changes in scope will affect pricing.
```

**Professional (for complex projects):**
```
1. Quotation valid until [DATE].
2. All prices exclusive of applicable taxes.
3. Payment Terms: 50% advance, 50% on completion.
4. Work commences only after advance receipt.
5. Variations beyond scope may incur additional charges.
6. Standard 12-month workmanship warranty applies.
```

---

## 🔄 Workflow Examples

### Scenario 1: Using Defaults Only
```
Estimator creates quotation:
├─ Fill in services/items
├─ Leave Terms/Guarantee/Payment empty
└─ Print PDF
    └─ System auto-fills from defaults ✓
```

### Scenario 2: Custom Terms for Special Client
```
Estimator creates quotation:
├─ Fill in services/items
├─ Fill custom Terms (client requirement)
├─ Custom Guarantee (special offer)
├─ Leave Payment blank (use default)
└─ Print PDF
    └─ Shows: Custom terms + Custom guarantee + Default payment ✓
```

### Scenario 3: Bulk Quotations with Defaults
```
Manager sets defaults:
├─ Set company-wide terms
├─ Set standard guarantee
├─ Set payment policy
└─ Save to settings

All future quotations:
└─ Auto-use these defaults
    └─ Saves time & ensures consistency ✓
```

---

## 🔧 Settings Reference

| Setting | Type | Default | Notes |
|---------|------|---------|-------|
| estimation_default_terms | Text | Empty | Multi-line text |
| estimation_default_guarantee | Text | Empty | Multi-line text |
| estimation_default_payment | Text | Empty | Multi-line text |

---

## 📧 Support & Troubleshooting

### Defaults not appearing?
1. Ensure you saved settings ✓
2. Check if quotation has its own terms (takes priority)
3. Clear browser cache
4. Try generating new quotation

### Text looks cut off in PDF?
1. Reduce text length
2. Use shorter point titles
3. Avoid very long sentences
4. Keep to 3-5 main points

### Styling looks different than preview?
1. Use Chrome's "Save as PDF" for best results
2. Ensure "Print backgrounds" is enabled in print dialog
3. Try different PDF viewer
4. Check display scaling (100%)

---

## Version & Updates
- **Updated**: May 20, 2026
- **Feature**: Default Terms, Guarantee, Payment with Fallback System
- **Scope**: All Quotation PDFs
- **Status**: ✅ Ready for Production

---

## Quick Links
- Admin Settings: `/admin/settings?tab=pdf`
- Quotation Module: `/admin/quotations`
- Guide: See `PDF_WATERMARK_SETUP.md` for other PDF features
