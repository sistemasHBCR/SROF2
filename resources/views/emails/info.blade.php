<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Notificación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
        }

        hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }

        .footer {
            font-size: 0.9em;
            color: #888;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Notificación programada</h1>
        <h2>{{ config('app.name') }}</h2>
        @foreach ($introLines as $line)
            <p>{{ $line }}</p>
        @endforeach
        <hr>
        <p class="footer">Este mensaje es únicamente como medio informativo en el sistema SRO. Por favor, no responda a
            este correo.</p>
    </div>
</body>

</html>
