<x-store.layout title="Entrar — Vevey Doces" :nav-categories="$navCategories">
    <div class="max-w-md mx-auto px-4 py-12 sm:py-16">
        <div class="bg-porcelain border hairline rounded-2xl p-6 sm:p-8 space-y-6">
            <div>
                <h1 class="font-display font-semibold text-2xl text-espresso">Entrar na sua conta</h1>
                <p class="text-sm text-espresso-soft mt-1">Acompanhe seus pedidos e endereços salvos na Vevey Doces.</p>
            </div>

            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('store.login.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-medium text-espresso mb-1.5">E-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                </div>
                <div>
                    <label for="password" class="block text-xs font-medium text-espresso mb-1.5">Senha</label>
                    <input id="password" name="password" type="password" required class="w-full bg-paper border hairline focus:border-espresso/40 focus:bg-porcelain rounded-lg px-3.5 h-10 text-sm text-espresso outline-none" />
                </div>
                <label class="flex items-center gap-2 text-xs text-espresso-soft cursor-pointer">
                    <input type="checkbox" name="remember" class="accent-brand-600 rounded" />
                    <span>Manter conectado</span>
                </label>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg h-11 transition-colors cursor-pointer">
                    Entrar
                </button>
            </form>

            <p class="text-xs text-center text-espresso-soft pt-2 border-t hairline">
                Ainda não tem conta?
                <a href="{{ route('store.register') }}" class="font-semibold text-brand-700 hover:underline">Criar conta grátis</a>
            </p>
        </div>
    </div>
</x-store.layout>
