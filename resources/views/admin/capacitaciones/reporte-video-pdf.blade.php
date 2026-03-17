<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $tituloReporte ?? 'Reporte por video' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 14px; margin-bottom: 8px; }
        p.sub { font-size: 9px; color: #6b7280; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $tituloReporte }}</h1>
    <p class="sub">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Especialista</th>
                <th>Email</th>
                <th>Fecha de visualización</th>
            </tr>
        </thead>
        <tbody>
            @forelse($completions as $c)
                <tr>
                    <td>{{ $c->user->name ?? '-' }}</td>
                    <td>{{ $c->user->email ?? '-' }}</td>
                    <td>{{ $c->completed_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Ningún especialista ha completado este video.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
