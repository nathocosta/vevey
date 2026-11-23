<x-store.layout title="Criar conta — Vevey Doces" :nav-categories="$navCategories">
    <div class="max-w-md mx-auto px-4 py-12 sm:py-16">
        <div class="bg-porcelain border hairline rounded-2xl p-6 sm:p-8 space-y-6">
            <div>
                <h1 class="font-display font-semibold text-2xl text-espresso">Criar conta</h1>
                <p class="text-sm text-espresso-soft mt-1">Se você já comprou conosco pelo site ou WhatsApp, use o mesmo e-mail para ver seu histórico.</p>
            </div>

            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('store.register.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-medium text-espresso mb-1.5">Nome completo</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                </div>
                <div>
                    <label for="email" class="block text-xs font-medium text-espresso mb-1.5">E-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label for="phone" class="block text-xs font-medium text-espresso mb-1.5">WhatsApp / Telefone</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required placeholder="(11) 99999-9999" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                    </div>
                    <div>
                        <label for="cpf" class="block text-xs font-medium text-espresso mb-1.5">CPF (opcional)</label>
                        <input id="cpf" name="cpf" type="text" maxlength="14" value="{{ old('cpf') }}" placeholder="000.000.000-00" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-medium text-espresso mb-1.5">Senha</label>
                        <input id="password" name="password" type="password" required minlength="6" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-espresso mb-1.5">Confirmar senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6" class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg h-11 transition-colors cursor-pointer">
                    Criar minha conta
                </button>
            </form>

            <p class="text-xs text-center text-espresso-soft pt-2 border-t hairline">
                Já tem conta?
                <a href="{{ route('store.login') }}" class="font-semibold text-brand-700 hover:underline">Entrar</a>
            </p>
        </div>
    </div>
</x-store.layout>
