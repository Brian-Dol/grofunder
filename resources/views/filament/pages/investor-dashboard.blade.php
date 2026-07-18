<x-filament-panels::page>
    @php
        $stats = $this->getPortfolioStats();
        $cooperatives = $this->getCooperativeBreakdown();
    @endphp

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Invested -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Invested</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ \Illuminate\Support\Number::currency($stats['total_invested'], 'ZMW') }}
                    </p>
                </div>
                <div class="rounded-lg bg-blue-100 p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Loans -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Loans</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['active_loans'] }}</p>
                </div>
                <div class="rounded-lg bg-green-100 p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Payment Rate -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Payment Rate</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['payment_rate'] }}%</p>
                    <p class="text-xs text-gray-500">{{ $stats['completed_payments'] }} completed</p>
                </div>
                <div class="rounded-lg bg-purple-100 p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Defaulted Loans -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Defaulted Loans</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['defaulted_loans'] }}</p>
                </div>
                <div class="rounded-lg bg-red-100 p-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Breakdown -->
    @if ($cooperatives)
        <div class="mt-8">
            <h2 class="text-lg font-bold text-gray-900">Portfolio by Cooperative</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full border-collapse rounded-lg border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-3 text-left text-sm font-semibold text-gray-900">
                                Cooperative Name
                            </th>
                            <th class="border border-gray-200 px-6 py-3 text-left text-sm font-semibold text-gray-900">
                                Region
                            </th>
                            <th class="border border-gray-200 px-6 py-3 text-right text-sm font-semibold text-gray-900">
                                Farmers
                            </th>
                            <th class="border border-gray-200 px-6 py-3 text-right text-sm font-semibold text-gray-900">
                                Active Loans
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cooperatives as $coop)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="border border-gray-200 px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $coop['name'] }}
                                </td>
                                <td class="border border-gray-200 px-6 py-4 text-sm text-gray-600">
                                    {{ $coop['region'] ?? 'N/A' }}
                                </td>
                                <td class="border border-gray-200 px-6 py-4 text-right text-sm text-gray-600">
                                    {{ $coop['farmers'] }}
                                </td>
                                <td class="border border-gray-200 px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    {{ $coop['active_loans'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="border border-gray-200 px-6 py-8 text-center text-sm text-gray-500">
                                    No cooperatives with active loans
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
