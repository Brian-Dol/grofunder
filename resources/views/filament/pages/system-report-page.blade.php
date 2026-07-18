<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">System-Wide Analytics</h1>
                <p class="text-gray-600 mt-1">{{ $reportData['period'] }}</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="exportPdf" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    📥 Export PDF
                </button>
                <button wire:click="exportCsv" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    📊 Export CSV
                </button>
            </div>
        </div>

        {{-- Date Range Filter --}}
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-600">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" wire:model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" wire:model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <button wire:click="generateReport" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    🔄 Generate Report
                </button>
            </div>
        </div>

        {{-- System-Wide Key Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Loans --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Loans</p>
                        <p class="text-3xl font-bold">{{ $reportData['loans']['total_loans'] }}</p>
                    </div>
                    <div class="text-4xl opacity-20">📋</div>
                </div>
            </div>

            {{-- Total Disbursed --}}
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Disbursed</p>
                        <p class="text-2xl font-bold">ZMW {{ number_format($reportData['loans']['total_disbursed'] / 1000000, 1) }}M</p>
                    </div>
                    <div class="text-4xl opacity-20">💰</div>
                </div>
            </div>

            {{-- Active Loans --}}
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Active Loans</p>
                        <p class="text-3xl font-bold">{{ $reportData['loans']['active_loans'] }}</p>
                    </div>
                    <div class="text-4xl opacity-20">⚡</div>
                </div>
            </div>

            {{-- Total Borrowers --}}
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Total Borrowers</p>
                        <p class="text-3xl font-bold">{{ $reportData['borrowers']['total_borrowers'] }}</p>
                    </div>
                    <div class="text-4xl opacity-20">👥</div>
                </div>
            </div>
        </div>

        {{-- Cooperatives Breakdown --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📍 Cooperatives Performance</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-4 text-gray-700 font-semibold">Cooperative</th>
                            <th class="text-center py-3 px-4 text-gray-700 font-semibold">Region</th>
                            <th class="text-right py-3 px-4 text-gray-700 font-semibold">Borrowers</th>
                            <th class="text-right py-3 px-4 text-gray-700 font-semibold">Total Loans</th>
                            <th class="text-right py-3 px-4 text-gray-700 font-semibold">Disbursed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['cooperatives'] as $coop)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-900">{{ $coop->name }}</td>
                                <td class="text-center py-3 px-4 text-gray-600">{{ $coop->region ?? 'N/A' }}</td>
                                <td class="text-right py-3 px-4 text-gray-600">{{ $coop->total_borrowers ?? 0 }}</td>
                                <td class="text-right py-3 px-4 text-gray-600">{{ $coop->total_loans ?? 0 }}</td>
                                <td class="text-right py-3 px-4 font-semibold">ZMW {{ number_format($coop->total_disbursed ?? 0, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500">No cooperative data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- System Loan Status --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Loans by Status --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Loan Status Distribution</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Active</span>
                            <span class="font-bold text-blue-600">{{ $reportData['loans']['active_loans'] }} loans</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full" style="width: {{ ($reportData['loans']['active_loans'] / max($reportData['loans']['total_loans'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Completed</span>
                            <span class="font-bold text-green-600">{{ $reportData['loans']['completed_loans'] }} loans</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-600 h-3 rounded-full" style="width: {{ ($reportData['loans']['completed_loans'] / max($reportData['loans']['total_loans'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Defaulted</span>
                            <span class="font-bold text-red-600">{{ $reportData['loans']['defaulted_loans'] }} loans</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-red-600 h-3 rounded-full" style="width: {{ ($reportData['loans']['defaulted_loans'] / max($reportData['loans']['total_loans'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Repayment Performance --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Repayment Performance</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b">
                        <span class="text-gray-600">Total Repayments</span>
                        <span class="font-bold">{{ $reportData['repayments']['total_repayments'] }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b">
                        <span class="text-gray-600">Total Repaid</span>
                        <span class="font-bold text-green-600">ZMW {{ number_format($reportData['repayments']['total_amount_repaid'], 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b">
                        <span class="text-gray-600">On-Time Rate</span>
                        <span class="font-bold text-lg">{{ $reportData['repayments']['on_time_rate'] }}%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">M-Pesa Success</span>
                        <span class="font-bold text-lg">{{ $reportData['mpesa']['success_rate'] }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- M-Pesa Metrics --}}
        <div class="bg-orange-50 border-l-4 border-orange-600 rounded-lg p-6">
            <h3 class="text-lg font-bold text-orange-900 mb-4">📱 M-Pesa Payment Analytics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded p-4">
                    <p class="text-gray-600 text-sm">Total Transactions</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $reportData['mpesa']['total_mpesa_transactions'] }}</p>
                </div>
                <div class="bg-white rounded p-4">
                    <p class="text-gray-600 text-sm">Total Amount</p>
                    <p class="text-2xl font-bold">ZMW {{ number_format($reportData['mpesa']['total_mpesa_amount'], 0) }}</p>
                </div>
                <div class="bg-white rounded p-4">
                    <p class="text-gray-600 text-sm">Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ $reportData['mpesa']['completed_transactions'] }}</p>
                </div>
                <div class="bg-white rounded p-4">
                    <p class="text-gray-600 text-sm">Success Rate</p>
                    <p class="text-2xl font-bold text-lg">{{ $reportData['mpesa']['success_rate'] }}%</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
