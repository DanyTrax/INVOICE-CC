<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $tituloReporte ?? 'Reporte de capacitaciones' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 14px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
        .date { font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>{{ $tituloReporte }}</h1>
    <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Agente</th>
                <th>Email</th>
                @foreach($videos as $v)
                    <th>{{ $v->titulo }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($agentes as $agente)
                <tr>
                    <td>{{ $agente->name }}</td>
                    <td>{{ $agente->email }}</td>
                    @foreach($videos as $video)
                        @php $c = $video->completions->firstWhere('user_id', $agente->id); @endphp
                        <td>{{ $c ? $c->completed_at->format('d/m/Y H:i') : '—' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
