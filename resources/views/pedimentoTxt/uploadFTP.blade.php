<!DOCTYPE html>
<html>
<head>
    <title>Subir Archivos al FTP</title>
    <!-- Incluye Bootstrap CSS para un mejor estilo -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Subir Archivos al FTP</h2>
    <form action="/upload-to-ftp" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="files">Selecciona archivos (TXT y PDF)</label>
            <input type="file" name="files[]" id="files" class="form-control" multiple accept=".txt,.pdf">
        </div>
        <button type="submit" class="btn btn-primary">Subir</button>
    </form>
</div>
</body>
</html>
