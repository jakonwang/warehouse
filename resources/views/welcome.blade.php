<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            body {
                font-family: 'Figtree', sans-serif;
                background-color: #f8fafc;
                color: #1a202c;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }
            .welcome {
                text-align: center;
                margin-top: 4rem;
            }
            .welcome h1 {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }
            .welcome p {
                font-size: 1.25rem;
                color: #4a5568;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="welcome">
                <h1>Welcome to Laravel</h1>
                <p>Your application is ready!</p>
            </div>
        </div>
    </body>
</html> 