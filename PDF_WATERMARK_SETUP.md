# Professional PDF Design Setup Guide

## Overview
Your CRM now supports a complete professional PDF design with watermarks, company seals, signatures, and enhanced terms & conditions display.

## Features Implemented

### 1. **Watermark Image** 🎨
- Upload your company logo or custom watermark image as PNG with transparent background
- Set opacity level (5% - 60%) for subtle to strong watermark effect
- Appears diagonally centered on all quotations
- If no image is set, falls back to text watermark (company name)

**Settings Location:** Admin → Settings → PDF & Document Settings → Watermark

### 2. **Company Seal & Signature** ✍️
- Upload authorized signature image
- Upload official company seal/stamp image
- Both images are displayed prominently in the signature section at the bottom of quotations
- Perfect for creating official-looking documents
- PNG images with transparent backgrounds recommended

**Settings Location:** Admin → Settings → PDF & Document Settings → Signature & Company Seal

### 3. **Enhanced Terms & Conditions** 📋
- Professional section for displaying Terms & Conditions
- Separate boxes for Terms, Guarantee/Warranty, and Payment Terms
- Color-coded headers for visual hierarchy
- Each section has distinct styling
- Supports multi-line text with proper formatting

### 4. **Professional Footer** 📄
- Customizable footer text displayed at bottom of every PDF
- Great for legal disclaimers, computer-generated notices, etc.
- Separate from header info

**Settings Location:** Admin → Settings → PDF & Document Settings → Colors & Footer

---

## How to Configure Everything

### Step 1: Upload Watermark Image

1. Go to **Admin Panel** → **Settings** → **PDF & Document Settings**
2. Find the **"Watermark"** section
3. Upload a PNG image (recommended: 300x300px with transparent background)
   - Examples: Company logo, "DRAFT", "APPROVED", "CONFIDENTIAL"
4. Adjust the **Opacity slider** (5% = subtle, 60% = strong)
   - Default: 15% (recommended for professional look)
5. Watch the **Live Preview** to see how it looks
6. Click **"Save Changes"**

### Step 2: Add Signature & Seal Images

1. In the same **PDF & Document Settings** page
2. Find **"Signature & Company Seal"** section
3. Upload two PNG images:
   - **Authorized Signature**: Your official signature (height 60-100px recommended)
   - **Company Seal/Stamp**: Official seal or stamp (100x200px recommended)
4. Both images appear in the signature block at the bottom of quotations
5. Click **"Save Changes"**

### Step 3: Configure Footer Text

1. Go to **Colors & Footer** section in same settings page
2. Enter your **PDF Footer Text** - examples:
   - "This is a computer-generated document. No signature is required."
   - "All terms and conditions apply as per our company policy."
   - "For questions, contact sales@company.com"
3. Keep text concise (1-2 lines)
4. Avoid special characters
5. Click **"Save Changes"**

### Step 4: Add Terms & Conditions to Quotations

When creating or editing a quotation:

1. Go to the quotation **Edit Page**
2. You'll see three fields:
   - **Terms & Conditions**: Main terms of service
   - **Guarantee**: Warranty or guarantee information
   - **Payment Terms**: Payment instructions and conditions
3. Fill in the relevant information
4. When you **Print/Export to PDF**, all three sections appear beautifully formatted

### Step 5: Choose PDF Header Color (Optional)

1. In **Colors & Footer** section
2. Select your preferred color for:
   - Table headers
   - Section titles
   - Decorative elements
3. Default: Indigo (#4f46e5)

---

## What Appears in the Printed PDF

### Document Structure:

```
┌─────────────────────────────────────────┐
│  COMPANY LOGO & INFO                    │
│  [Watermark Image appears here]         │
├─────────────────────────────────────────┤
│  EST-2024-001 | STATUS                  │
├─────────────────────────────────────────┤
│  Customer Details | Quotation Details   │
├─────────────────────────────────────────┤
│  Items/Services Table                   │
├─────────────────────────────────────────┤
│  Subtotal | Tax | Total Amount          │
├─────────────────────────────────────────┤
│  📋 Terms, Conditions & Guarantee       │
│  ┌─────────────┬─────────────┬────────┐ │
│  │ Terms & C   │ Guarantee   │Payment │ │
│  │             │             │        │ │
│  └─────────────┴─────────────┴────────┘ │
├─────────────────────────────────────────┤
│  Bank Details | UPI / QR Code           │
├─────────────────────────────────────────┤
│  Authorized Signature | Company Seal    │ │  Customer Acceptance | Date │
├─────────────────────────────────────────┤
│  Footer Text + Company Info             │
│  Generated on: [Date & Time]            │
└─────────────────────────────────────────┘
```

---

## Best Practices

### Watermark Image
✅ **DO:**
- Use company logo for professional look
- Use PNG with transparent background
- Keep file size under 200KB
- Use opacity 10-20% for subtle effect
- Test in print preview

❌ **DON'T:**
- Use colored watermark (looks unprofessional)
- Use JPEG (no transparency support)
- Use watermark larger than 350px
- Set opacity above 40% (makes document hard to read)

### Signature & Seal
✅ **DO:**
- Scan actual signature at 300 DPI
- Use PNG format for transparency
- Keep seal circular or standard company shape
- Size appropriately (signature 60-100px height, seal 100-200px)

❌ **DON'T:**
- Use handwritten signatures directly (unprofessional)
- Use low-resolution images
- Make seal too large (distorts document)

### Terms & Conditions
✅ **DO:**
- Keep text clear and concise
- Use 2-4 bullet points max per section
- Include important legal information
- Use professional language

❌ **DON'T:**
- Use overly long text
- Use complex legal jargon
- Include special formatting
- Leave sections empty

---

## Troubleshooting

### Watermark Not Showing?
1. Check if image was uploaded successfully
2. Verify opacity is not set to 0%
3. Clear browser cache (Ctrl+F5)
4. Try re-uploading the image

### Signature/Seal Images Not Appearing?
1. Confirm both images are uploaded
2. Check file format is PNG
3. Verify file size is reasonable
4. Try re-uploading with correct dimensions

### Footer Text Looks Cut Off?
1. Reduce text length
2. Remove special characters
3. Keep to 1-2 lines maximum
4. Test in print preview

### PDF Prints Differently Than Preview?
1. Check browser print settings
2. Ensure "Print backgrounds" is enabled
3. Try different PDF reader
4. Test with Chrome's "Save as PDF" option

---

## Settings Reference

| Setting | Default | Notes |
|---------|---------|-------|
| Watermark Opacity | 15% | Range: 5-60% |
| PDF Header Color | #4f46e5 | Indigo (professional) |
| PDF Footer Text | "This is a computer generated document." | Customizable |
| Watermark Image | None | PNG recommended |
| Signature Image | None | PNG with transparency |
| Company Seal | None | PNG with transparency |

---

## Support

For issues or questions:
1. Check this guide's Troubleshooting section
2. Test with a simple quotation first
3. Try different images if issues persist
4. Contact support with screenshot of problem

---

## Version
Updated: 2026-05-20
Supports: All Quotation PDFs
