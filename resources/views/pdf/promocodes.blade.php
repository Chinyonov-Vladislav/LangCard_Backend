<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Таблица промокодов</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            margin: 20px;
        }

        .promo-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .promo-table th,
        .promo-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        .promo-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .promo-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .days-count {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>Таблица промокодов</h1>

<table class="promo-table">
    <thead>
    <tr>
        <th>Промокод</th>
        <th>Наименование тарифа</th>
        <th>Количество дней</th>
    </tr>
    </thead>
    <tbody>
    @foreach($promocodes as $promo)
        <tr>
            <td class="promo-code">{{ $promo['code'] }}</td>
            <td>{{ $promo['tariff']['name'] }}</td>
            <td class="days-count">{{ $promo['tariff']['days'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
