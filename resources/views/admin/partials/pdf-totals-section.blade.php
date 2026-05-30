@php
    $sideNotePlain = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($sideNoteHtml ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    $hasSideNote = $sideNotePlain !== '';
@endphp
<table class="totals-layout" width="100%" cellspacing="0" cellpadding="0" style="width:100%; table-layout:fixed;">
    <colgroup>
        <col style="width:520px;">
        <col style="width:240px;">
    </colgroup>
    <tr>
        <td valign="top" style="width:520px; vertical-align:top; padding-right:14px;">
            @if($hasSideNote)
                <div class="side-note">{!! $sideNoteHtml !!}</div>
            @endif
        </td>
        <td valign="top" align="right" class="totals-col" style="width:240px; vertical-align:top; text-align:right;">
            @include('admin.partials.pdf-totals-box', [
                'doc' => $doc,
                'fmt' => $fmt,
                'subtotalLabel' => $subtotalLabel ?? 'Subtotal',
            ])
        </td>
    </tr>
</table>
