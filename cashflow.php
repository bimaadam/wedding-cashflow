<?php
$pageTitle = "Cashflow";
ob_start();
?>

<!-- Tailwind CSS CDN for styling -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<div class="container mx-auto p-6">
    <!-- Header -->
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Cashflow Dashboard</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold text-gray-700">Total Income</h2>
            <p class="text-2xl font-bold text-green-600">$12,345.67</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold text-gray-700">Total Expenses</h2>
            <p class="text-2xl font-bold text-red-600">$8,765.43</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold text-gray-700">Net Cashflow</h2>
            <p class="text-2xl font-bold text-blue-600">$3,580.24</p>
        </div>
    </div>

    <!-- Add Transaction Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Add New Transaction</h2>
        <form action="process_cashflow.php" method="POST" class="space-y-4">
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                <input type="number" id="amount" name="amount" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add Transaction</button>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Transactions</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Sample Data (Replace with dynamic PHP data) -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-07-21</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Salary</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">Income</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$5,000.00</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-07-20</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Groceries</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">Expense</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$150.00</td>
                    </tr>
                    <!-- Add more rows dynamically using PHP -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout/main.php';
?>