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
            border-radius: 1vh;
        }

        .successMessage
        {
            color: green;
            position: absolute;
            left: 50%;
            top: 30%;
            transform: translate(-50%);
        }

        #progressContainer
        {
            position: absolute;
            left: 50%;
            top: 40%;
            transform: translate(-50%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 50px;    
            width: 100%;       
        }

        progress
        {
            margin-top: 10px;
        }

        .uploadedNotification
        {
            color: green;
            display: none;
        }

        .invalidRowsButton
        {
            display: none;
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

        {{-- Form for CSV Upload. --}}
        <form action="{{ route('csv.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- <input type="hidden" name="_token" value="somerandomtokenhere"> This defines a CSRF token field autmatically. It would look like this in HTML. --}}
            <input type="file" name="csv_file" accept=".csv" required>
            <br><br>
            <button type="submit">Upload</button>
        </form>

        {{-- Loading bar.  --}}
        <div id="progressContainer" style="display:none;">
            <progress id="progressBar" max="100" value="0"></progress>
        <div id="progressText">0% complete</div>

        <h4 class="uploadedNotification">
            CSV file has been validated and is saved to the database.
        </h4>

        @if(session('filename'))
            <a href="{{ route('csv.invalid', ['filename' => session('filename')]) }}" class="invalidRowsButton">View Invalid Rows</a>
        @endif

        {{-- Javascript to animate loading bar. --}}
        <script>
            // Retrieve the filename from the session
            let fileName = "{{ session('filename') }}";

            if (fileName) {
                // Function to fetch and update the progress
                function updateProgress() {
                    fetch(`/csv-progress/${fileName}`)
                    .then(response => response.json())
                    .then(data => {
                        let progress = Math.round((data.current / data.total) * 100);
                        let valid = data.valid;
                        let invalid = data.invalid;

                        // Update progress bar
                        let progressContainer = document.getElementById("progressContainer");
                        let progressBar = document.getElementById("progressBar");
                        let progressText = document.getElementById("progressText");
                        let successMessage = document.getElementsByClassName("successMessage")[0];
                        let validationSuccess = document.getElementsByClassName("uploadedNotification")[0];
                        let invalidRowsLink = document.getElementsByClassName("invalidRowsButton")[0];

                        progressContainer.style.display = 'flex';

                        progressBar.value = progress;
                        progressText.innerText = `${progress}% complete (Valid: ${valid}, Invalid: ${invalid})`;

                        // Check if the progress is complete and hide the progress bar if done
                        if (progress === 100) {
                            clearInterval(progressInterval); // Stop the interval when complete
                            //progressContainer.style.display = 'none';
                            successMessage.style.display = 'none';
                            validationSuccess.style.display = 'flex';
                            invalidRowsLink.style.display = 'flex';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching progress:', error);
                    });
                }

                // Update progress every 2 seconds
                let progressInterval = setInterval(updateProgress, 200);
            }
        </script>
    </body>
</html>
