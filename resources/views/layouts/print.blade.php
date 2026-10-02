<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PKB {{ $document['number'] }} · TEFA Automotive</title>
        <style>
            * { box-sizing: border-box; }
            body { margin: 0; color: #172321; font-family: Arial, sans-serif; font-size: 12px; }
            .sheet { max-width: 190mm; margin: 20px auto; }
            .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #126b56; padding-bottom: 14px; }
            h1 { margin: 0; font-size: 22px; letter-spacing: 0; }
            h2 { margin: 20px 0 8px; font-size: 13px; text-transform: uppercase; }
            p { margin: 4px 0; line-height: 1.45; }
            .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; }
            .block { border: 1px solid #cbd6d1; padding: 10px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border-bottom: 1px solid #dce5e1; padding: 7px 6px; text-align: left; }
            th { background: #edf3ef; }
            .right { text-align: right; }
            .notes { min-height: 58mm; border: 1px solid #aebbb5; margin-top: 8px; }
            .actions { display: flex; justify-content: center; gap: 10px; padding: 16px; }
            button, a { border: 1px solid #126b56; border-radius: 4px; background: #126b56; color: white; padding: 9px 14px; font: inherit; text-decoration: none; cursor: pointer; }
            a { background: white; color: #126b56; }
            @media print { .actions { display: none; } .sheet { margin: 0 auto; } }
            @media screen and (max-width: 640px) { .sheet { margin: 12px; } .grid { grid-template-columns: 1fr; } }
        </style>
    </head>
    <body>
        <div class="actions print-actions">
            <a href="{{ route('admin.pkb.show', request()->route('pkb')) }}">Kembali ke detail</a>
            <button type="button" onclick="window.print()">Cetak PKB</button>
        </div>
        <main class="sheet">@yield('content')</main>
    </body>
</html>