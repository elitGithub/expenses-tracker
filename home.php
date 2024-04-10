<?php

declare(strict_types = 1);


?>

<div class="container py-5">
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-5">
        <!-- Dynamic PHP Content for Cards will go here -->
        <?php
        $timeframe = $_GET['timeframe'] ?? 'monthly'; // Default to monthly
        $data = [
            'all time'  => ['budget' => 5000, 'expenses' => 4500, 'categories' => 10, 'topCategory' => 'Food'],
            'yearly'    => ['budget' => 1000, 'expenses' => 800, 'categories' => 8, 'topCategory' => 'Utilities'],
            'quarterly' => ['budget' => 500, 'expenses' => 450, 'categories' => 5, 'topCategory' => 'Travel'],
            'monthly'   => ['budget' => 200, 'expenses' => 180, 'categories' => 4, 'topCategory' => 'Entertainment'],
        ];
        $selectedData = $data[$timeframe];

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
        labels: ['Budget', 'Expenses', 'Categories', 'Top Category'],
        datasets: [{
            label: '<?= $timeframe?> data',
            data: [<?=$selectedData['budget']?>, <?=$selectedData['expenses']?>, <?=$selectedData['categories']?>, 0], // 0 for 'Top Category' as it's not numerical
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)'
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


