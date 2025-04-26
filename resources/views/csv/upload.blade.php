<!DOCTYPE html>
<html>
<head>
    <title>Upload CSV</title>
    <meta charset="UTF-8">
</head>
<style>
    html, body
    {
        background: rgb(48, 48, 48);
        color: whitesmoke;
    }
    h1
    {
        position: absolute;
        left: 50%;
        transform: translate(-50%);
    }
    form
    {
        position: absolute;
        left: 50%;
        transform: translate(-50%, 200%);
    }
    button
    {
        width: 7vw;
        height: 4vh;
        position: absolute;
        left: 50%;
        transform: translate(-50%);
        border: none;
        border-radius: 1vh
    }
    button:hover
    {
        background: rgb(107, 107, 107);
        color: white;
    }
    input
    {
        background: none;
        color: white;
        width: 15vw;
        height: 4vh;
        position: absolute;
        left: 50%;
        transform: translate(-50%);
        border-radius: 1vh
    }
    .successMessage
    {
        color: green;
        position: absolute;
        left: 50%;
        top: 30%;;
        transform: translate(-50%);
    }
</style>
<body>
    <h1>Upload a CSV File</h1>

    {{-- Colors success message green --}}
    @if(session('status'))
        <p class="successMessage">{{ session('status') }}</p>
    @endif

    {{-- Displays list of upload errors in red. --}}
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
        {{-- <input type="hidden" name="_token" value="somerandomtokenhere"> This defines a CSRF token field autmatically. It would look like this in HTML. --}}
        <input type="file" name="csv_file" accept=".csv" required>
        <br><br>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
