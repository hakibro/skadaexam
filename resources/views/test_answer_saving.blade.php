<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Answer Saving</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <h1>Answer Saving Test</h1>

    <div id="test-results"></div>

    <button onclick="testAnswerSaving()">Test Answer Saving</button>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function testAnswerSaving() {
            const results = document.getElementById('test-results');
            results.innerHTML = '<p>Testing...</p>';

            try {
                // Test data - you'll need to replace these with actual IDs from your database
                const testData = {
                    hasil_ujian_id: 1, // Replace with actual hasil_ujian_id
                    soal_ujian_id: 1, // Replace with actual soal_ujian_id
                    jawaban: 'A'
                };

                console.log('Sending request with data:', testData);
                console.log('CSRF Token:', csrfToken);

                const response = await fetch('/ujian/save-answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(testData)
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    result = {
                        error: 'Invalid JSON response',
                        raw: responseText
                    };
                }

                if (response.ok) {
                    results.innerHTML = '<p style="color: green;">✅ Success: ' + JSON.stringify(result) + '</p>';
                } else {
                    results.innerHTML = '<p style="color: red;">❌ Error (' + response.status + '): ' + JSON.stringify(
                        result) + '</p>';
                }

            } catch (error) {
                console.error('Network error:', error);
                results.innerHTML = '<p style="color: red;">❌ Network Error: ' + error.message + '</p>';
            }
        }
    </script>
</body>

</html>
