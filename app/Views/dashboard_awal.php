<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    .stat-card {
        transition: all 0.3s ease;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(145deg, #ffffff, #f5f7fa);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .trend-badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .chart-card {
        border-radius: 16px;
        background: linear-gradient(145deg, #ffffff, #f5f7fa);
    }
    .btn-filter {
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 500;
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-modern tbody tr {
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: all 0.2s;
    }
    .table-modern tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .table-modern td {
        border: none;
        padding: 16px;
    }
    .table-modern td:first-child {
        border-radius: 8px 0 0 8px;
    }
    .table-modern td:last-child {
        border-radius: 0 8px 8px 0;
    }
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .action-btn:hover {
        background: #f0f2f5;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-3">
      <div class="col">
        <div class="card card-body">
          <form action="#" id="form-filter" class="form-horizontal">
            <div class="row">
              <!-- Dropdown Sumber -->
              <div class="col-3">
                <div class="form-group">
                  <select class="form-control" name="dbs" id="dbs" style="width: 100%">
                    <?php 
                    $dbList = getSelDb();
                    foreach ($dbList as $key => $name): ?>
                        <option value="<?= $key ?>"><?= $name ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <!-- Dropdown Tahun -->
              <div class="col-3">
                <div class="form-group">
                  <select class="form-control select2" name="tahun" id="tahun" style="width: 100%">
                    <?php for ($i = date('Y'); $i >= $startYear; $i--): ?>
                      <option value="<?=$i?>" <?=($i == $thnSkg) ? "selected" : ""?>><?=$i?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>
              <div class="col-2">
                <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> <?=isLang('filter')?></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>     

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-book-open text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Pendapatan</h6>
                        <h3 class="card-title mb-1">1,234</h3>
                        <div class="small text-muted">Current Period</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-balance-scale text-success fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total HPP</h6>
                        <h3 class="card-title mb-1">$876,543</h3>
                        <div class="small text-muted">Current Period</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-balance-scale-right text-info fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Biaya</h6>
                        <h3 class="card-title mb-1">$876,543</h3>
                        <div class="small text-muted">Current Period</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Laba/Rugi</h6>
                        <h3 class="card-title mb-1">12</h3>
                        <div class="small text-warning">Needs Review</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Account Balances Chart -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Account Balances Overview</h5>
                    <div class="d-flex justify-content-end mb-3">
                        <select class="form-select form-select-sm w-auto">
                            <option>Last 30 Days</option>
                            <option>Last Quarter</option>
                            <option>Last Year</option>
                        </select>
                    </div>
                    <canvas id="accountBalancesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Monthly Transaction Volume -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Transaction Volume</h5>
                    <div class="d-flex justify-content-end mb-3">
                        <select class="form-select form-select-sm w-auto">
                            <option>By Month</option>
                            <option>By Quarter</option>
                            <option>By Year</option>
                        </select>
                    </div>
                    <canvas id="transactionVolumeChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Journal Entries -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Recent Journal Entries</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#journalEntryModal">
                    <i class="fas fa-plus me-1"></i>New Entry
                </button>
            </div>
            <div class="table-responsive">
                <table id="journalEntriesTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample data - Replace with dynamic data -->
                        <tr>
                            <td>2025-02-19</td>
                            <td>JE-2025-0001</td>
                            <td>
                                <div class="fw-bold">Monthly Rent Payment</div>
                                <small class="text-muted">Office Space Rent</small>
                            </td>
                            <td class="text-end">$5,000.00</td>
                            <td class="text-end">$5,000.00</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success">Posted</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


    <!-- GL Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="d-grid gap-2 d-md-flex">
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#journalEntryModal">
                            <i class="fas fa-plus-circle me-2"></i>New Journal Entry
                        </button>
                        <button class="btn btn-light">
                            <i class="fas fa-book me-2"></i>Chart of Accounts
                        </button>
                        <button class="btn btn-light">
                            <i class="fas fa-file-export me-2"></i>Trial Balance
                        </button>
                        <button class="btn btn-light">
                            <i class="fas fa-chart-bar me-2"></i>Financial Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Period Status</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1">Current Period: <strong>February 2025</strong></p>
                            <p class="mb-1">Status: <span class="badge bg-success">Open</span></p>
                            <p class="mb-0">Last Closing: January 31, 2025</p>
                        </div>
                        <button class="btn btn-warning">
                            <i class="fas fa-lock me-2"></i>Close Period
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Journal Entry Modal -->
<div class="modal fade" id="journalEntryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title">New Journal Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="journalEntryForm" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No.</label>
                            <input type="text" class="form-control" readonly value="JE-2025-0002">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Account</th>
                                    <th>Description</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="journalEntryLines">
                                <tr>
                                    <td>
                                        <select class="form-select" required>
                                            <option value="">Select Account</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control"></td>
                                    <td><input type="number" class="form-control text-end"></td>
                                    <td><input type="number" class="form-control text-end"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Line
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="journalEntryForm" class="btn btn-primary">Save Entry</button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $('#journalEntriesTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'collection',
                text: '<i class="fas fa-download me-1"></i>Export',
                className: 'btn-light',
                buttons: ['excel', 'pdf']
            }
        ]
    });
    // Account Balances Chart
    new Chart(document.getElementById('accountBalancesChart'), {
        type: 'bar',
        data: {
            labels: ['Assets', 'Liabilities', 'Equity', 'Revenue', 'Expenses'],
            datasets: [{
                label: 'Account Balances',
                data: [500000, 200000, 300000, 450000, 350000],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Transaction Volume Chart
    new Chart(document.getElementById('transactionVolumeChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Number of Transactions',
                data: [65, 59, 80, 81, 56, 55],
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>