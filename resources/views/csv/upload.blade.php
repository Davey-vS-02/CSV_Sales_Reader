<!DOCTYPE html>
<html>
<head>
    <title>Upload CSV</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Upload a CSV File</h1>

    {{-- Colors success message green --}}
    @if(session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    {{-- Displays errors fo upload in red. --}}
    @if($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- Form for CSV Upload --}}
    <form action="{{ route('csv.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="csv_file" accept=".csv" required>
        <br><br>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
