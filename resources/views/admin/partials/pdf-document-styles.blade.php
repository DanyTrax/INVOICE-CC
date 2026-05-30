<style>
    @page { size: letter; margin: 0; }
    html { margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 14mm 12mm; line-height: 1.45; }
    .pdf-letterhead {
        position: fixed;
        top: 0;
        left: 0;
        /* Carta US @ 96dpi (DomPDF): 8.5in × 11in */
        width: 816px;
        height: 1056px;
        margin: 0;
        padding: 0;
        z-index: -1000;
        background-repeat: no-repeat;
        background-position: top left;
        background-size: 816px 1056px;
    }
    .pdf-letterhead img {
        display: block;
        width: 816px;
        height: 1056px;
        margin: 0;
        padding: 0;
        border: 0;
    }
    .pdf-body-content { position: relative; z-index: 1; }
    h1.doc-title { font-size: 11px; font-weight: bold; margin: 0 0 8px 0; color: #111827; text-align: center; }
    .context-body { margin-bottom: 10px; line-height: 1.45; font-size: 11px; color: #1f2937; }
    .context-body * { font-family: DejaVu Sans, sans-serif !important; font-size: 11px !important; color: #1f2937 !important; }
    .side-note { font-size: 11px; line-height: 1.45; color: #1f2937; }
    .side-note * { font-family: DejaVu Sans, sans-serif !important; font-size: 11px !important; }
    .closing-footer { margin-top: 14px; font-size: 11px; line-height: 1.45; color: #1f2937; }
    .closing-footer * { font-family: DejaVu Sans, sans-serif !important; font-size: 11px !important; }
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 14px; table-layout: fixed; }
    table.items th { background: #ccfbf1; text-align: left; padding: 6px 5px; font-size: 11px; font-weight: bold; text-transform: uppercase; border: 1px solid #99f6e4; word-wrap: break-word; }
    table.items td { padding: 5px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; }
    table.items tr.alt { background: #f9fafb; }
    .totals-wrap { margin-top: 12px; overflow: hidden; }
    .totals-wrap .side-note-col { float: left; width: 58%; padding-right: 8px; box-sizing: border-box; }
    .totals-wrap .totals-col { float: right; width: 40%; }
    .totals { width: 100%; }
    .totals table { width: 100%; border-collapse: collapse; }
    .totals td { padding: 5px 6px; border: 1px solid #e5e7eb; font-size: 11px; }
    .totals .label { background: #f3f4f6; font-weight: bold; width: 55%; }
    .totals .grand { background: #e5e7eb; font-weight: bold; }
    .signature { margin-top: 28px; padding-top: 0; clear: both; }
    .signature-line { width: 240px; border-bottom: 1px solid #1f2937; margin-top: 28px; margin-bottom: 4px; }
    .meta { margin-bottom: 8px; font-size: 11px; }
    .meta p { margin: 3px 0; }
</style>
