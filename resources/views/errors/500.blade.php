<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Erro inesperado — Vevey Doces</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-paper text-espresso antialiased flex items-center justify-center p-6">
    <main class="max-w-md w-full bg-porcelain border hairline rounded-2xl p-8 text-center space-y-5 shadow-[var(--shadow-subtle)]">
        <p class="font-mono text-xs uppercase tracking-widest text-brand-700 font-semibold">Erro 500</p>
        <h1 class="font-display font-semibold text-2xl text-espresso">Algo saiu do esperado</h1>
        <p class="text-sm text-espresso-soft leading-relaxed">
            Tivemos uma instabilidade momentânea ao processar sua solicitação. Tente novamente em alguns instantes.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg px-5 h-10 transition-colors">
                Voltar ao início
            </a>
        </div>
    </main>
</body>
</html>
