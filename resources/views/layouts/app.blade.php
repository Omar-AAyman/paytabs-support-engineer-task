<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayTabs Demo Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="app-container">
        <nav class="navbar">
            <div class="logo">PayTabs <span class="highlight">Demo</span></div>
            <div class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Orders</a>
                <a href="{{ route('orders.create') }}"
                    class="{{ request()->routeIs('orders.create') ? 'active' : '' }}">New Order</a>
            </div>
        </nav>

        <main class="content">
            @if (session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
