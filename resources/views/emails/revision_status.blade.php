<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        .header { background-color: #1a202c; color: #ffffff; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .status-badge { 
            display: inline-block; 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 12px;
        }
        .PROCESO { background-color: #fef3c7; color: #92400e; } /* Ámbar */
        .ESPERA { background-color: #fee2e2; color: #991b1b; } /* Rojo */
        .LIBERADA { background-color: #d1fae5; color: #065f46; } /* Verde */
        .footer { background-color: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #718096; }
        .details { margin-top: 20px; border-top: 1px solid #edf2f7; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Notificación de Revisión</h2>
        </div>
        <div class="content">
            <p>Se te informa que la revisión del tráfico <strong>#{{ $trafico->id }}</strong> ha cambiado de estado:</p>
            
            <div style="text-align: center; margin: 20px 0;">
                <span class="status-badge {{ str_contains($status, 'PROCESO') ? 'PROCESO' : (str_contains($status, 'ESPERA') ? 'ESPERA' : 'LIBERADA') }}">
                    {{ $status }}
                </span>
            </div>

            <div class="details">
                <p><strong>Detalles del Tráfico:</strong></p>
                <ul>
                    <li><strong>Factura:</strong> {{ $trafico->factura ?? 'N/A' }}</li>
                    <li><strong>Cliente:</strong> {{ $trafico->empresa->nombre ?? 'N/A' }}</li>
                    <li><strong>Fecha de actualización:</strong> {{ now()->format('d/m/Y H:i') }}</li>
                </ul>
            </div>

            <p>Puedes revisar los detalles completos en el sistema SAI.</p>
        </div>
        <div class="header" style="background-color: #f9fafb; color: #718096; font-size: 11px;">
            Este es un correo automático, por favor no respondas a este mensaje.
        </div>
    </div>
</body>
</html>