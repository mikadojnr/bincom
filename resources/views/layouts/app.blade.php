<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Election Results') &middot; Bincom</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, Segoe UI, Arial, sans-serif; margin: 0; background: #f1f5f9; color: #1e293b; }
        header { background: #0f172a; color: #fff; padding: 18px 24px; }
        header h1 { margin: 0; font-size: 20px; }
        header small { color: #94a3b8; font-weight: 400; }
        nav { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
        nav a { color: #cbd5e1; text-decoration: none; padding: 7px 14px; border-radius: 6px; font-size: 14px; }
        nav a:hover { background: #1e293b; color: #fff; }
        nav a.active { background: #2563eb; color: #fff; }
        main { max-width: 960px; margin: 24px auto; padding: 0 16px 40px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        h2 { margin: 0 0 4px; font-size: 18px; }
        .muted { color: #64748b; font-size: 13px; margin: 0 0 16px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
        select, input[type=text] { width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #fff; }
        select:disabled { background: #f1f5f9; color: #94a3b8; }
        button { background: #2563eb; color: #fff; border: 0; padding: 10px 18px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 14px; }
        th, td { text-align: left; padding: 9px 12px; border-bottom: 1px solid #e2e8f0; }
        thead th { background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .03em; }
        tbody tr:hover { background: #f8fafc; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        tfoot td { font-weight: 700; border-top: 2px solid #cbd5e1; }
        .party { font-weight: 600; }
        .flash { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; }
        .error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; }
        .error ul { margin: 6px 0 0; padding-left: 18px; }
        .actions { margin-top: 18px; }
        dl.meta { display: grid; grid-template-columns: auto 1fr; gap: 6px 16px; margin: 0; font-size: 14px; }
        dl.meta dt { color: #64748b; }
        dl.meta dd { margin: 0; font-weight: 600; }
        .empty { color: #64748b; font-style: italic; }
    </style>
    @stack('head')
</head>
<body>
<header>
    <h1>Bincom Election Results <small>&middot; Delta State (2011)</small></h1>
    <nav>
        <a href="{{ route('pu.result') }}" class="{{ request()->routeIs('pu.result') ? 'active' : '' }}">Polling Unit Result</a>
        <a href="{{ route('lga.summary') }}" class="{{ request()->routeIs('lga.summary') ? 'active' : '' }}">LGA Summed Result</a>
        <a href="{{ route('pu.create') }}" class="{{ request()->routeIs('pu.create') ? 'active' : '' }}">New Polling Unit</a>
    </nav>
</header>
<main>
    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="error">
            Please fix the following:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
<script>
    // Shared helpers for the chained combo boxes.
    async function fetchOptions(url) {
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        return response.ok ? response.json() : [];
    }

    function fillSelect(select, items, placeholder, selected) {
        select.innerHTML = '';
        select.add(new Option(placeholder, ''));
        items.forEach(function (item) {
            const label = item.number ? item.name + ' (' + item.number + ')' : item.name;
            const option = new Option(label, item.id);
            if (selected && String(selected) === String(item.id)) option.selected = true;
            select.add(option);
        });
        select.disabled = items.length === 0;
    }
</script>
@stack('scripts')
</body>
</html>
