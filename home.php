<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategory;
use ExpenseTracker\ExpenseCategoryList;
use ExpenseTracker\ExpenseList;

$timeframe = $_GET['timeframe'] ?? 'monthly'; // Default to monthly
$expenseCategoryList = new ExpenseCategoryList();
$expenseList = new ExpenseList();
?>

<div class="container py-5">
    <div class="mb-4">
        <form action="" method="GET">
            <label for="timeframe">Select time to display</label>
            <select class="form-select" id="timeframe" name="timeframe" onchange="this.form.submit()">
                <option value="all time" <?php if ($timeframe === 'all time') echo 'selected'; ?>>All Time</option>
                <option value="yearly" <?php if ($timeframe === 'yearly') echo 'selected'; ?>>Yearly</option>
                <option value="quarterly" <?php if ($timeframe === 'quarterly') echo 'selected'; ?>>Quarterly</option>
                <option value="monthly" <?php if ($timeframe === 'monthly') echo 'selected'; ?>>Monthly</option>
            </select>
        </form>
    </div>
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-5">
        <!-- Dynamic PHP Content for Cards will go here -->
        <?php
        $totalBudget = $expenseCategoryList->getBudgetForTimeFrame($timeframe);
        $totalExpenses = $expenseList->getExpensesByTimeFrame($timeframe);
        $totalCategories = $expenseCategoryList->countTotalCategoriesByTimeFrame($timeframe);
        $selectedData = ['budget' => $totalBudget, 'expenses' => $totalExpenses, 'categories' => $totalCategories];

        // Generating Cards
        foreach ($selectedData as $key => $value) {
            echo "<div class='col'><div class='card'><div class='card-body'>";
            echo "<h5 class='card-title'>" . ucfirst($key) . "</h5>";
            echo "<p class='card-text'>" . $value . "</p>";
            echo "</div></div></div>";
        }
        ?>
    </div>

    <!-- Chart Section -->
    <div class="chart-container" style="position: relative; height:40vh; width:80vw">
        <canvas id="expenseChart"></canvas>
    </div>
</div>

<script>
    // Dynamically setting up the Chart
    const data = {
        labels: ['Budget', 'Expenses', 'Categories'],
        datasets: [{
            label: '<?= $timeframe?> data',
            data: [<?=$selectedData['budget']?>, <?=$selectedData['expenses']?>, <?=$selectedData['categories']?>], // 0 for 'Top Category' as it's not numerical
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    };

    const config = {
        type: 'bar',
        data,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
    };

    const expenseChart = new Chart(
        document.getElementById('expenseChart'),
        config
    );
</script>


