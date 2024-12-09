<!doctype html>
<html lang="en">
    <head>
        <title>Title</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />
    </head>

    <body>
        <header>
            <!-- place navbar here -->
        </header>
        <main>
                @foreach ($matches as $image)
                <img src="{{ asset('storage/user_images/' . $image) }}" alt="{{ $image }}" width="300px">
            @endforeach
        
        </main>
        <footer>
            <!-- place footer here -->
        </footer>
    </body>
</html>
