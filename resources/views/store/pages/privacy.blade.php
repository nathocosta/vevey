<x-store.layout
    title="Política de Privacidade (LGPD) — Vevey Doces"
    description="Como a Vevey Doces coleta, protege e utiliza seus dados pessoais em conformidade com a Lei Geral de Proteção de Dados (LGPD)."
    :nav-categories="$navCategories"
>
    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-8">
        <header class="space-y-2 border-b hairline pb-6">
            <p class="text-xs font-medium uppercase tracking-widest text-brand-700">Privacidade e LGPD</p>
            <h1 class="font-display font-semibold text-3xl text-espresso">Política de Privacidade</h1>
            <p class="text-xs text-espresso-muted">Última atualização: {{ date('Y') }} · Em conformidade com a Lei nº 13.709/2018 (LGPD)</p>
        </header>

        <div class="bg-porcelain border hairline rounded-2xl p-6 sm:p-8 space-y-6 text-sm text-espresso-soft leading-relaxed">
            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">1. Dados coletados</h2>
                <p>
                    Coletamos apenas os dados estritamente necessários para processar seus pedidos, calcular entregas e autenticar sua conta: nome completo, e-mail, telefone/WhatsApp, endereço de entrega e, opcionalmente, CPF para emissão fiscal.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">2. Segurança e armazenamento</h2>
                <p>
                    Todas as senhas são armazenadas com hash criptográfico irreversível, a autenticação conta com verificação em duas etapas (2FA) por código temporário e todas as comunicações em produção trafegam exclusivamente sobre HTTPS (TLS) com cabeçalhos de proteção contra XSS, clickjacking e CSRF.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">3. Cookies e armazenamento local</h2>
                <p>
                    Utilizamos cookies estritamente necessários para manter sua sessão autenticada e proteção CSRF, além de armazenamento local no navegador (`localStorage`) exclusivamente para preservar seu carrinho de compras e sua lista de favoritos. Não vendemos dados para terceiros.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="font-display font-semibold text-base text-espresso">4. Seus direitos como titular</h2>
                <p>
                    Você pode consultar, corrigir ou atualizar seus dados diretamente em <a href="{{ route('store.account') }}" class="text-brand-700 font-medium hover:underline">Minha Conta</a>, ou solicitar a exclusão definitiva dos seus registros pelo e-mail <strong class="text-espresso">privacidade@veveydoces.com.br</strong>.
                </p>
            </section>
        </div>
    </article>
</x-store.layout>
