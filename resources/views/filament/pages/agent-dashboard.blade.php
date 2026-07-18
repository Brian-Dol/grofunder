<x-filament-panels::page>
    @php
        $cooperative = $this->getCooperativeInfo();
        $stats = $this->getFarmerStats();
        $farmers = $this->getFarmersList();
        $activity = $this->getRecentActivity();
    @endphp

    @if ($cooperative)
        <!-- Cooperative Header -->
        <div class="mb-8 rounded-lg border border-blue-200 bg-blue-50 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $cooperative['name'] }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="inline-block bg-blue-100 px-3 py-1 rounded-full text-xs font-medium text-blue-800">
                            {{ ucfirst($cooperative['status']) }}
                        </span>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">
                        <strong>Region:</strong> {{ $cooperative['region'] ?? 'N/A' }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Phone:</strong> {{ $cooperative['contact_phone'] ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Key Statistics -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
            <!-- Total Farmers -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Farmers</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total_farmers'] }}</p>
                    </div>
                    <div class="rounded-lg bg-indigo-100 p-3">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 10H9m6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Farmers with Loans -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">With Loans</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total_farmers_with_loans'] }}</p>
                    </div>
                    <div class="rounded-lg bg-green-100 p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
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
                    <div class="rounded-lg bg-blue-100 p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- On-Time Payments -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">On-Time</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['on_time_loans'] }}</p>
                    </div>
                    <div class="rounded-lg bg-emerald-100 p-3">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overdue Loans -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Defaulted</p>
                        <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['overdue_loans'] }}</p>
                    </div>
                    <div class="rounded-lg bg-red-100 p-3">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Farmers List -->
        @if ($farmers)
            <div class="mt-8">
                <h2 class="text-lg font-bold text-gray-900">Farmers in Your Cooperative</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse rounded-lg border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border border-gray-200 px-6 py-3 text-left text-sm font-semibold text-gray-900">
                                    Farmer Name
                                </th>
                                <th class="border border-gray-200 px-6 py-3 text-left text-sm font-semibold text-gray-900">
                                    Farmer ID (Mobile)
                                </th>
                                <th class="border border-gray-200 px-6 py-3 text-left text-sm font-semibold text-gray-900">
                                    Contact
                                </th>
                                <th class="border border-gray-200 px-6 py-3 text-right text-sm font-semibold text-gray-900">
                                    Active Loans
                                </th>
                                <th class="border border-gray-200 px-6 py-3 text-right text-sm font-semibold text-gray-900">
                                    Total Borrowed
                                </th>
                                <th class="border border-gray-200 px-6 py-3 text-right text-sm font-semibold text-gray-900">
                                    Total Repaid
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($farmers as $farmer)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="border border-gray-200 px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $farmer['name'] }}
                                    </td>
                                    <td class="border border-gray-200 px-6 py-4 text-sm">
                                        <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                            {{ $farmer['mobile_number'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="border border-gray-200 px-6 py-4 text-sm text-gray-600">
                                        {{ $farmer['mobile'] ?? 'N/A' }}
                                    </td>
                                    <td class="border border-gray-200 px-6 py-4 text-right text-sm font-semibold">
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                                            {{ $farmer['active_loans'] }}
                                        </span>
                                    </td>
                                    <td class="border border-gray-200 px-6 py-4 text-right text-sm text-gray-900">
                                        {{ \Illuminate\Support\Number::currency($farmer['total_borrowed'], 'ZMW') }}
                                    </td>
                                    <td class="border border-gray-200 px-6 py-4 text-right text-sm text-gray-900">
                                        {{ \Illuminate\Support\Number::currency($farmer['total_repaid'], 'ZMW') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="border border-gray-200 px-6 py-8 text-center text-sm text-gray-500">
                                        No farmers assigned to your cooperative yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Recent Activity -->
        @if ($activity)
            <div class="mt-8">
                <h2 class="text-lg font-bold text-gray-900">Recent Loan Activity</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($activity as $item)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $item['farmer_name'] }}</p>
                                <p class="text-xs text-gray-500">Loan #{{ $item['loan_number'] }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ \Illuminate\Support\Number::currency($item['amount'], 'ZMW') }}
                                </span>
                                <span class="rounded-full px-3 py-1 text-xs font-medium
                                    {{ $item['status'] === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $item['status'] === 'partially_paid' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $item['status'] === 'fully_paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $item['status'] === 'defaulted' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $item['status'])) }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $item['updated_at'] }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-gray-200 bg-white p-4 text-center text-sm text-gray-500">
                            No recent loan activity
                        </p>
                    @endforelse
                </div>
            </div>
        @endif
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-center">
            <p class="text-sm text-amber-800">
                ⚠️ Your user account is not assigned to a cooperative. Please contact an administrator.
            </p>
        </div>
    @endif
</x-filament-panels::page>
