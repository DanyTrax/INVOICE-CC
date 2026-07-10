<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->number }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px;text-align:center;background:#0f766e;">
                            @if($brand->logoUrl())
                                <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->company_name }}" style="max-height:56px;">
                            @else
                                <span style="color:#fff;font-size:20px;font-weight:bold;">{{ $brand->company_name }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;color:#111827;font-size:15px;line-height:1.6;">
                            {!! $bodyHtml !!}
                            <p style="margin-top:24px;color:#6b7280;font-size:13px;">
                                Adjuntamos el PDF de su cuenta de cobro <strong>{{ $invoice->number }}</strong>.
                                @if($brand->support_email)
                                    Para soporte escríbanos a <a href="mailto:{{ $brand->support_email }}">{{ $brand->support_email }}</a>.
                                @endif
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f9fafb;color:#6b7280;font-size:12px;text-align:center;">
                            {{ $brand->company_name }} · {{ $brand->city }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
