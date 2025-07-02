<?php require_once 'partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once 'partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= translate('dashboard'); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary"><?= date('F Y'); ?></button>
                    </div>
                </div>
            </div>
            
            <!-- Notifications -->
            <?php if(isset($_SESSION['new_badge'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas <?= $_SESSION['new_badge']['icon']; ?> me-2" style="color: <?= $_SESSION['new_badge']['color']; ?>"></i>
                    <?= sprintf(translate('new_badge_earned'), $_SESSION['new_badge']['name']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['new_badge']); ?>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['unusual_spending'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= sprintf(translate('unusual_spending'), $_SESSION['unusual_spending']['category'], formatCurrency($_SESSION['unusual_spending']['amount'])); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['unusual_spending']); ?>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= translate('current_balance'); ?></h5>
                            <h2 class="card-text"><?= formatCurrency($data['balance']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= translate('monthly_income'); ?></h5>
                            <h2 class="card-text"><?= formatCurrency($data['monthly_income']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= translate('monthly_expenses'); ?></h5>
                            <h2 class="card-text"><?= formatCurrency($data['monthly_expenses']); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><?= translate('expenses_by_category'); ?></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="expensesChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><?= translate('balance_history'); ?></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="balanceChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Badges and Top Savers -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><?= translate('your_badges'); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($data['badges'])): ?>
                                <div class="d-flex flex-wrap gap-3">
                                    <?php foreach($data['badges'] as $badge): ?>
                                        <div class="badge-item" data-bs-toggle="tooltip" title="<?= $badge->description; ?>">
                                            <i class="fas <?= $badge->icon; ?> fa-2x" style="color: <?= $badge->color; ?>"></i>
                                            <div class="small mt-1"><?= $badge->name; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted"><?= translate('no_badges_yet'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><?= translate('top_savers'); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($data['top_savers'])): ?>
                                <div class="list-group">
                                    <?php foreach($data['top_savers'] as $index => $saver): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-primary me-2"><?= $index + 1; ?></span>
                                                <?= htmlspecialchars($saver->username); ?>
                                            </div>
                                            <span class="badge bg-success"><?= formatCurrency($saver->savings); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted"><?= translate('no_savers_data'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/chart-config.js"></script>
<script>
    // Configurer les graphiques avec les données PHP
    document.addEventListener('DOMContentLoaded', function() {
        // Camembert des dépenses par catégorie
        const expensesCtx = document.getElementById('expensesChart').getContext('2d');
        const expensesChart = new Chart(expensesCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($data['expenses_by_category'], 'name')); ?>,
                datasets: [{
                    data: <?= json_encode(array_column($data['expenses_by_category'], 'total')); ?>,
                    backgroundColor: <?= json_encode(array_column($data['expenses_by_category'], 'color')); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        
        // Courbe d'évolution du solde
        const balanceCtx = document.getElementById('balanceChart').getContext('2d');
        const balanceChart = new Chart(balanceCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($data['balance_history'], 'month')); ?>,
                datasets: [{
                    label: '<?= translate('balance'); ?>',
                    data: <?= json_encode(array_column($data['balance_history'], 'balance')); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
        
        // Activer les tooltips pour les badges
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php require_once 'partials/footer.php'; ?>