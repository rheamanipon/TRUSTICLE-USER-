<?php include_once '../includes/header.php'; ?>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">Analytics</h1>
        </div>
        
        <div class="analytics-content">
            <div class="welcome-message">
                <h1>Analytics Overview</h1>
                <p>Monitor article distribution and keyword trends.</p>
            </div>
            
            <!-- Stats Grid - 4 Cards -->
            <div class="stats-grid">
                <!-- Total Users Card -->
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
                
                <!-- Total Submitted Articles Card -->
                <div class="stat-card yellow">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Submitted Articles</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
                
                <!-- Total Approved Articles Card -->
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Approved Articles</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
                
                <!-- Total Pending Articles Card -->
                <div class="stat-card red">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Pending Articles</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Grid - Donut and Line Charts -->
            <div class="chart-grid">
                <!-- Donut Chart - Article Distribution -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Article Distribution</div>
                    </div>
                    <div class="donut-container">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
                
                <!-- Line Chart - Top Fake Keywords -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Top Fake Keywords</div>
                    </div>
                    <div class="line-chart-container">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>