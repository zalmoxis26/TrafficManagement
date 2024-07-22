<!DOCTYPE html>
<html>
<head>
    <title>Nueva Factura Procesada</title>
</head>
<body>
    <h1>Se ha procesado una nueva factura</h1>
    <p><strong>Factura:</strong> {{ $trafico->factura }}</p>
    <p><strong>Empresa ID:</strong> {{ $trafico->empresa->descripcion }}</p>
    <p><strong>Fecha de Registro:</strong> {{ $trafico->fechaReg }}</p>
    <p><strong>Aduana:</strong> {{ $trafico->aduana }}</p>
    <p><strong>Patente:</strong> {{ $trafico->patente }}</p>
    <p><strong>Tipo de Operación:</strong> {{ $trafico->Toperacion }}</p>
</body>
</html>
