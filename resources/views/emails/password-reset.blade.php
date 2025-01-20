
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecimiento de Contraseña</title>
</head>
<body>
    <h1>Restablecimiento de Contraseña</h1>
    @foreach ($introLines as $line)
        <p>{{ $line }}</p>
    @endforeach
    <p>Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna acción adicional.</p>
</body>
</html>
