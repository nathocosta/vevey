<x-filament-panels::page>
    <style>
        .vevey-audit-filters {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.875rem;
            align-items: end;
        }
        @media (min-width: 768px) {
            .vevey-audit-filters {
                grid-template-columns: 180px 220px 1fr auto;
            }
        }
        .vevey-audit-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
            opacity: 0.85;
        }
        .vevey-audit-table-wrap {
            overflow-x: auto;
        }
        .vevey-audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .vevey-audit-table th {
            text-align: left;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            opacity: 0.65;
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
            white-space: nowrap;
        }
        .vevey-audit-table td {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            vertical-align: top;
        }
        .vevey-audit-table tr:hover td {
            background: rgba(148, 163, 184, 0.06);
        }
        .vevey-audit-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.78rem;
            white-space: nowrap;
            opacity: 0.8;
        }
        .vevey-audit-note {
            font-size: 0.78rem;
            opacity: 0.7;
            margin-top: 0.2rem;
        }
    </style>

    <x-filament::section heading="Filtros de auditoria" description="Rastreie alterações no estoque (ledger) e mudanças de status de pedidos (RNF011)">
        <div class="vevey-audit-filters">
            <div>
                <label class="vevey-audit-label">Módulo</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="module">
                        <option value="all">Todos os módulos</option>
                        <option value="inventory">Somente Estoque</option>
                        <option value="orders">Somente Pedidos</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="vevey-audit-label">Responsável</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="userId">
                        <option value="">Todos os usuários</option>
                        <option value="system">Sistema / Cliente (site)</option>
                        @foreach ($this->staffUsers as $staff)
                            <option value="{{ $staff['id'] }}">{{ $staff['name'] }} ({{ $staff['email'] }})</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="vevey-audit-label">Buscar</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Número do pedido, SKU, produto ou observação..."
                    />
                </x-filament::input.wrapper>
            </div>

            <div>
                <x-filament::button wire:click="resetFilters" color="gray" outlined>
                    Limpar
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section :heading="'Eventos registrados (' . count($this->events) . ')'">
        @if ($this->events === [])
            <div style="padding: 2.5rem 1rem; text-align: center; opacity: 0.7; font-size: 0.875rem;">
                Nenhum evento encontrado com os filtros selecionados.
            </div>
        @else
            <div class="vevey-audit-table-wrap">
                <table class="vevey-audit-table">
                    <thead>
                        <tr>
                            <th>Data / Hora</th>
                            <th>Módulo</th>
                            <th>Responsável</th>
                            <th>Ação registrada</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->events as $event)
                            <tr wire:key="{{ $event['id'] }}">
                                <td class="vevey-audit-mono">{{ $event['when'] }}</td>
                                <td>
                                    <x-filament::badge :color="$event['badge_color']">
                                        {{ $event['module_label'] }}
                                    </x-filament::badge>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $event['actor'] }}</div>
                                    @if ($event['actor_email'])
                                        <div style="font-size: 0.73rem; opacity: 0.65;">{{ $event['actor_email'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ $event['summary'] }}</div>
                                    @if ($event['detail'])
                                        <div class="vevey-audit-note">Motivo/Obs.: {{ $event['detail'] }}</div>
                                    @endif
                                </td>
                                <td style="white-space: nowrap; text-align: right;">
                                    @if ($event['url'])
                                        <x-filament::link :href="$event['url']" size="sm" weight="semibold">
                                            {{ $event['url_label'] }}
                                        </x-filament::link>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (count($this->events) >= $limit)
                <div style="margin-top: 1rem; text-align: center;">
                    <x-filament::button wire:click="loadMore" color="gray" size="sm">
                        Carregar mais eventos
                    </x-filament::button>
                </div>
            @endif
        @endif
    </x-filament::section>
</x-filament-panels::page>
