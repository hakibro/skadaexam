<!-- This file tests the redirection behavior for siswa login -->
<!DOCTYPE html>
<html>

<head>
    <title>Siswa Login Redirect Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .info {
            background-color: #e3f2fd;
        }

        .error {
            background-color: #ffebee;
        }

        .success {
            background-color: #e8f5e9;
        }

        h2 {
            margin-top: 0;
        }

        pre {
            background: #f5f5f5;
            padding: 10px;
            overflow: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Siswa Login Redirect Test</h1>

        <div class="card info">
            <h2>Auth Status</h2>
            @if (auth()->guard('siswa')->check())
                <p><strong>Siswa guard:</strong> Authenticated as {{ auth()->guard('siswa')->user()->nama }}</p>
            @else
                <p><strong>Siswa guard:</strong> Not authenticated</p>
            @endif

            @if (auth()->guard('web')->check())
                <p><strong>Web guard:</strong> Authenticated as {{ auth()->guard('web')->user()->name }}</p>
            @else
                <p><strong>Web guard:</strong> Not authenticated</p>
            @endif
        </div>

        <div class="card info">
            <h2>Route Information</h2>
            <p><strong>Current route name:</strong> {{ Route::currentRouteName() }}</p>
            <p><strong>Current URL:</strong> {{ url()->current() }}</p>
            <p><strong>Siswa dashboard route:</strong> {{ route('siswa.dashboard') }}</p>
        </div>

        <div class="card">
            <h2>Login Form</h2>
            <form method="POST" action="{{ route('login.siswa.submit') }}">
                @csrf
                <div>
                    <label for="idyayasan">ID Yayasan:</label>
                    <input type="text" id="idyayasan" name="idyayasan" value="{{ old('idyayasan') }}">
                </div>
                <div>
                    <label for="token">Token:</label>
                    <input type="text" id="token" name="token">
                </div>
                <button type="submit">Login</button>
            </form>

            @if ($errors->any())
                <div class="card error">
                    <h3>Errors</h3>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="card success">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
