<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>
<body class="antialiased">
<table>
    <tr>
        <th>char_code</th>
        <th>name</th>
        <th>value</th>
        <th>difference</th>
    </tr>
    @foreach($currencies as $currency)
        @php /** @var \App\Models\Currency $currency*/ @endphp
        <tr>
            <td>{{ $currency->char_code }}</td>
            <td>{{ $currency->name }}</td>
            <td>{{ $currency->value }}</td>
            <td></td>
        </tr>
    @endforeach
</table>
</body>
</html>
