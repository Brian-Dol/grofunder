<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $reportData['cooperative']->name }} Report</h1>
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
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-600">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" wire:model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" wire:model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <button wire:click="generateReport" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    🔄 Generate Report
                </button>
            </div>
        </div>

        {{-- Key Metrics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Total Loans --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Loans</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $reportData['loans']['total_loans'] }}</p>
                    </div>
                    <div class="text-blue-600 text-3xl">📋</div>
                </div>
                <p class="text-green-600 text-sm mt-2">+{{ $reportData['loans']['active_loans'] }} Active</p>
            </div>

            {{-- Total Disbursed --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Disbursed</p>
                        <p class="text-3xl font-bold text-gray-900">ZMW {{ number_format($reportData['loans']['total_disbursed'], 0) }}</p>
                    </div>
                    <div class="text-green-600 text-3xl">💰</div>
                </div>
                <p class="text-gray-600 text-sm mt-2">Avg: ZMW {{ number_format($reportData['loans']['average_loan_amount'], 0) }}</p>
            </div>

            {{-- Repayment Rate --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">On-Time Rate</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $reportData['repayments']['on_time_rate'] }}%</p>
                    </div>
                    <div class="text-purple-600 text-3xl">✅</div>
                </div>
                <p class="text-green-600 text-sm mt-2">{{ $reportData['repayments']['on_time_repayments'] }} on time</p>
            </div>

            {{-- M-Pesa Success --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">M-Pesa Success</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $reportData['mpesa']['success_rate'] }}%</p>
                    </div>
                    <div class="text-orange-600 text-3xl">📱</div>
                </div>
                <p class="text-gray-600 text-sm mt-2">{{ $reportData['mpesa']['total_mpesa_transactions'] }} transactions</p>
            </div>

            {{-- Total Borrowers --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Borrowers</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $reportData['borrowers']['total_borrowers'] }}</p>
                    </div>
                    <div class="text-indigo-600 text-3xl">👥</div>
                </div>
                <p class="text-green-600 text-sm mt-2">{{ $reportData['borrowers']['active_borrowers'] }} active</p>
            </div>
        </div>

        {{-- Loan Status Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Loans by Status --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Loan Status Breakdown</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Active Loans</span>
                            <span class="font-bold">{{ $reportData['loans']['active_loans'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($reportData['loans']['active_loans'] / max($reportData['loans']['total_loans'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Completed Loans</span>
                            <span class="font-bold">{{ $reportData['loans']['completed_loans'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($reportData['loans']['completed_loans'] / max($reportData['loans']['total_loans'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Defaulted Loans</span>
                            <span class="font-bold">{{ $reportData['loans']['defaulted_loans'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ ($reportData['loans']['defaulted_loans'] / max($reportData['loans']['total_loans'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Revenue Metrics --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Revenue & Collection</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b">
                        <span class="text-gray-600">Total Interest Earned</span>
                        <span class="font-bold text-green-600">ZMW {{ number_format($reportData['revenue']['total_interest_earned'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b">
                        <span class="text-gray-600">Expected Repayments</span>
                        <span class="font-bold">ZMW {{ number_format($reportData['revenue']['expected_repayments'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b">
                        <span class="text-gray-600">Actual Repayments</span>
                        <span class="font-bold">ZMW {{ number_format($reportData['revenue']['actual_repayments'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-gray-600 font-medium">Collection Rate</span>
                        <span class="font-bold text-lg">{{ $reportData['revenue']['collection_rate'] }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Borrowers --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Performing Borrowers</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-4 text-gray-600">Borrower Name</th>
                            <th class="text-right py-2 px-4 text-gray-600">Completed Loans</th>
                            <th class="text-right py-2 px-4 text-gray-600">Total Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['topBorrowers'] as $borrower)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $borrower->name }}</td>
                                <td class="text-right py-3 px-4 font-semibold">{{ $borrower->loans_count ?? 0 }}</td>
                                <td class="text-right py-3 px-4">ZMW {{ number_format($borrower->loans_sum_principal_amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 px-4 text-center text-gray-500">No borrower data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Borrowers at Risk --}}
        @if($reportData['atRiskBorrowers']->count() > 0)
            <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-6">
                <h3 class="text-lg font-bold text-red-900 mb-4">⚠️ Borrowers at Risk (Overdue)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-red-200">
                                <th class="text-left py-2 px-4 text-red-800">Borrower</th>
                                <th class="text-right py-2 px-4 text-red-800">Loan #</th>
                                <th class="text-right py-2 px-4 text-red-800">Outstanding</th>
                                <th class="text-right py-2 px-4 text-red-800">Days Overdue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['atRiskBorrowers'] as $borrower)
                                @foreach($borrower->loans as $loan)
                                    <tr class="border-b border-red-100">
                                        <td class="py-3 px-4">{{ $borrower->name }}</td>
                                        <td class="text-right py-3 px-4">{{ $loan->loan_number }}</td>
                                        <td class="text-right py-3 px-4 font-semibold">ZMW {{ number_format($loan->balance, 2) }}</td>
                                        <td class="text-right py-3 px-4 text-red-600 font-bold">{{ now()->diffInDays($loan->due_date) }} days</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
