<!DOCTYPE html>
<html>
<head>
    <title>Invalid CSV Rows</title>
    <meta charset="UTF-8">
    <style>
        html, body {
            background: rgb(48, 48, 48);
            color: white;
            font-family: Arial, sans-serif;
            font-size: 1.5vh;
        }

        h1
        {
            padding-top: 2vh;
            font-size: 6vh;
        }

        table {
            width: 80%;
            margin: 50px auto;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid white;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: rgb(20, 20, 65);
        }

        tr:nth-child(even) {
            background-color: rgb(60, 60, 60);
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Invalid Rows</h1>

    @if($invalidRows->isEmpty())
        <p style="text-align: center;">No invalid rows found for this file.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Store</th>
                    <th>Store Code</th>
                    <th>Date</th>
                    <th>Order Num</th>
                    <th>Receipt Num</th>
                    <th>Receipt Count</th>
                    <th>Balance</th>
                    <th>Delivery Date</th>
                    <th>Commission Earned</th>
                    <th>Product Code</th>
                    <th>Head and base included</th>
                    <th>Head, base and frame included</th>
                    <th>Complete</th>
                    <th>Color</th>
                    <th>Direct</th>
                    <th>Photo granite/perspex</th>
                    <th>Wall included</th>
                    <th>Comments</th>
                    <th>Error Column</th>
                    <th>Error Message</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invalidRows as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->store }}</td>
                        <td>{{ $row->store_code }}</td>
                        <td>{{ $row->date }}</td>
                        <td>{{ $row->order_num }}</td>
                        <td>{{ $row->receipt_num }}</td>
                        <td>{{ $row->receipt_count }}</td>
                        <td>{{ $row->balance_outstanding }}</td>
                        <td>{{ $row->delivery_date }}</td>
                        <td>{{ $row->commission_earned }}</td>
                        <td>{{ $row->product_code }}</td>
                        <td>{{ $row->head_base_included }}</td>
                        <td>{{ $row->head_base_frame_included }}</td>
                        <td>{{ $row->completion_status }}</td>
                        <td>{{ $row->product_color }}</td>
                        <td>{{ $row->direct }}</td>
                        <td>{{ $row->photo }}</td>
                        <td>{{ $row->wall_included }}</td>
                        <td>{{ $row->comments }}</td>
                        <td>{{ $row->error_column }}</td>
                        <td>{{ $row->error_message }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
