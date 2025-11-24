<style>
    .panel-light {
        background: #f8f9fc;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
        padding: 20px;
        margin-bottom: 20px;
    }
    .panel-title {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 15px;
        color: #4e73df;
    }
    .form-label {
        font-weight: 600;
        font-size: 13px;
    }
    .form-control {
        border-radius: 6px;
        height: 40px;
    }
</style>

{{-- BLOQUE 1 — DATOS DEL CLIENTE --}}
<div class="panel-light">
    <div class="panel-title">Datos del Cliente</div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">CLAVE CLIENTE</label>
            <input type="text" name="clave" class="form-control @error('clave') is-invalid @enderror"
                   value="{{ old('clave', $empresa?->clave) }}">
            {!! $errors->first('clave', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="col-md-9 mb-3">
            <label class="form-label">NOMBRE DEL CLIENTE</label>
            <input type="text" name="empresaMatriz" class="form-control @error('empresaMatriz') is-invalid @enderror"
                   value="{{ old('empresaMatriz', $empresa?->empresaMatriz) }}">
            {!! $errors->first('empresaMatriz', '<div class="invalid-feedback">:message</div>') !!}
        </div>
    </div>
</div>

{{-- BLOQUE 2 — DATOS DEL PROVEEDOR --}}
<div class="panel-light">
    <div class="panel-title">Datos del Proveedor</div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">CLAVE PROVEEDOR</label>
            <input type="text" name="claveProveedor" class="form-control @error('claveProveedor') is-invalid @enderror"
                   value="{{ old('claveProveedor', $empresa?->claveProveedor) }}">
            {!! $errors->first('claveProveedor', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="col-md-9 mb-3">
            <label class="form-label">NOMBRE DEL PROVEEDOR</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                   value="{{ old('descripcion', $empresa?->descripcion) }}">
            {!! $errors->first('descripcion', '<div class="invalid-feedback">:message</div>') !!}
        </div>
    </div>
</div>

{{-- BLOQUE 3 — FACTURACIÓN Y NOTIFICACIONES --}}
<div class="panel-light">
    <div class="panel-title">Facturación y Notificaciones</div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">PREFIJO FACTURA</label>
            <input type="text" name="prefijoFactura" class="form-control @error('prefijoFactura') is-invalid @enderror"
                   value="{{ old('prefijoFactura', $empresa?->prefijoFactura) }}">
            {!! $errors->first('prefijoFactura', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">RFC</label>
            <input type="text" name="rfc" class="form-control @error('rfc') is-invalid @enderror"
                   value="{{ old('rfc', $empresa?->rfc) }}">
            {!! $errors->first('rfc', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">CORREOS PARA NOTIFICACIÓN</label>
            <input type="text" name="emailNotify" class="form-control @error('emailNotify') is-invalid @enderror"
                   value="{{ old('emailNotify', $empresa?->emailNotify) }}"
                   placeholder="correo1@ejemplo.com, correo2@ejemplo.com">
            <small class="text-muted">Separa varios correos con coma.</small>
            {!! $errors->first('emailNotify', '<div class="invalid-feedback">:message</div>') !!}
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <button type="submit" class="btn btn-primary px-4 py-2">
        {{ isset($empresa) ? 'Actualizar' : 'Guardar' }}
    </button>
</div>
