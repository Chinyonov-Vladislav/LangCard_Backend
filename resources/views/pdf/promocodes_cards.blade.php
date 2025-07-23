<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Промокоды</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 8px;
        }

        .cards-table {
            width: 100%;
            height: 100%;
            table-layout: fixed;
            border-collapse: separate;  /* ИЗМЕНЕНИЕ: Изменено с collapse на separate */
            border-spacing: 0 3mm;
        }

        .cards-table td {
            width: 25%;
            height: 22%;
            border: 1px solid #333;
            padding: 2mm;
            vertical-align: middle;
            text-align: center;
            box-sizing: border-box;
        }

        .card-title {
            font-size: 9px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2mm;
            text-transform: uppercase;
            line-height: 1.1;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }

        .promo-code {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 1.5mm;
            border: 1px dashed #6c757d;
            margin: 2mm 0;
            letter-spacing: 0.5px;
            word-break: break-all;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.2;
        }

        .card-info {
            font-size: 10px;
            color: #6c757d;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .days-info {
            font-weight: bold;
            color: #28a745;
            margin-top: 1mm;
        }
    </style>
</head>
<body>
<table class="cards-table">
    @php
        $chunks = $promocodes->chunk(4);
    @endphp

    @foreach($chunks as $row)
        <tr>
            @foreach($row as $promo)
                <td>
                    <div class="card-title">
                        {{ $promo['tariff']['name'] }}
                    </div>

                    <div class="promo-code">
                        {{ $promo['code'] }}
                    </div>

                    <div class="card-info">
                        <div class="days-info">
                            Дней: {{ $promo['tariff']['days'] }}
                        </div>
                    </div>
                </td>
            @endforeach

            @for($i = 0; $i < (4 - count($row)); $i++)
                <td></td>
            @endfor
        </tr>
    @endforeach

    @for($i = 0; $i < (2 - count($chunks)); $i++)
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endfor
</table>
</body>
</html>
