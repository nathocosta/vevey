<x-store.layout title="Verificação em 2 etapas — Vevey Doces" :nav-categories="$navCategories">
    <div class="max-w-md mx-auto px-4 py-12 sm:py-16">
        <div class="bg-porcelain border hairline rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">verified_user</span>
                </span>
                <div>
                    <h1 class="font-display font-semibold text-xl text-espresso">Verificação em 2 etapas</h1>
                    <p class="text-xs text-espresso-soft mt-1">
                        Digite o código de 6 dígitos enviado para <strong class="text-espresso">{{ $email }}</strong>.
                    </p>
                </div>
            </div>

            @if (session('status'))
                <div class="p-3.5 rounded-xl bg-positive-soft text-positive text-xs font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if ($demoCode)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                    <p class="font-semibold">Modo demonstração (sem SMTP configurado)</p>
                    <p>Seu código de verificação de 6 dígitos é:</p>
                    <p class="font-mono text-2xl font-bold tracking-[0.25em] text-espresso pt-1">{{ $demoCode }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('store.verify.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="block text-xs font-medium text-espresso mb-1.5">Código de 6 dígitos</label>
                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        pattern="\d{6}"
                        required
                        autofocus
                        placeholder="000000"
                        class="w-full bg-paper border hairline-strong focus:border-brand-600 focus:bg-porcelain rounded-xl px-4 h-12 text-center font-mono text-2xl tracking-[0.35em] text-espresso outline-none"
                    />
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg h-11 transition-colors cursor-pointer">
                    Confirmar código e ativar conta
                </button>
            </form>

            <form method="POST" action="{{ route('store.verify.resend') }}" class="text-center pt-2 border-t hairline">
                @csrf
                <button type="submit" class="text-xs font-medium text-espresso-soft hover:text-brand-700 underline cursor-pointer">
                    Não recebeu? Gerar novo código
                </button>
            </form>
        </div>
    </div>
</x-store.layout>
