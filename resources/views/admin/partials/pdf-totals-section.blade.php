<table class="totals-layout" width="100%" cellspacing="0" cellpadding="0">
    @if(trim(strip_tags($sideNoteHtml ?? '')) !== '')
        <colgroup>
            <col width="430">
            <col width="240">
        </colgroup>
        <tr>
            <td valign="top" style="width:430px; vertical-align:top; padding-right:12px;">
                <div class="side-note">{!! $sideNoteHtml !!}</div>
            </td>
            <td valign="top" align="right" style="width:240px; vertical-align:top; text-align:right;">
                @include('admin.partials.pdf-totals-box', [
                    'doc' => $doc,
                    'fmt' => $fmt,
                    'subtotalLabel' => $subtotalLabel ?? 'Subtotal',
                ])
            </td>
        </tr>
    @else
        <tr>
            <td valign="top" align="right" style="text-align:right;">
                @include('admin.partials.pdf-totals-box', [
                    'doc' => $doc,
                    'fmt' => $fmt,
                    'subtotalLabel' => $subtotalLabel ?? 'Subtotal',
                ])
            </td>
        </tr>
    @endif
</table>
