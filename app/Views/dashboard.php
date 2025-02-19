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
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-dollar-sign text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase fs-sm">Total Assets</h6>
                            <h3 class="mb-1 fw-bold">$1,234,567</h3>
                            <div class="d-flex align-items-center">
                                <span class="trend-badge bg-success-subtle text-success me-2">
                                    <i class="fas fa-arrow-up me-1"></i>8.3%
                                </span>
                                <small class="text-muted">vs last month</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="fas fa-file-invoice-dollar text-danger fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Liabilities</h6>
                        <h3 class="card-title mb-1">$876,543</h3>
                        <div class="small text-danger">
                            <i class="fas fa-arrow-down"></i> 2.4% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-chart-pie text-success fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Equity</h6>
                        <h3 class="card-title mb-1">$358,024</h3>
                        <div class="small text-success">
                            <i class="fas fa-arrow-up"></i> 4.1% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-chart-line text-info fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Net Income</h6>
                        <h3 class="card-title mb-1">$42,891</h3>
                        <div class="small text-success">
                            <i class="fas fa-arrow-up"></i> 12.5% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Monthly Revenue vs Expenses</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary">Week</button>
                            <button class="btn btn-sm btn-primary">Month</button>
                            <button class="btn btn-sm btn-outline-secondary">Year</button>
                        </div>
                    </div>
                    <canvas id="revenueExpensesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Account Distribution</h5>
                        <button class="btn btn-sm btn-light" title="Download Report">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <canvas id="accountDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Recent Transactions</h5>
                <a href="/transactions" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> New Transaction
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2023-07-01</td>
                            <td>
                                <div class="fw-bold">Office Supplies</div>
                                <small class="text-muted">Purchase order #1234</small>
                            </td>
                            <td>Expenses</td>
                            <td><span class="badge bg-danger rounded-pill">Debit</span></td>
                            <td class="text-end fw-bold">$234.50</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2023-07-01</td>
                            <td>
                                <div class="fw-bold">Client Payment</div>
                                <small class="text-muted">Invoice #5678</small>
                            </td>
                            <td>Revenue</td>
                            <td><span class="badge bg-success rounded-pill">Credit</span></td>
                            <td class="text-end fw-bold">$1,500.00</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2023-06-30</td>
                            <td>
                                <div class="fw-bold">Utility Bill</div>
                                <small class="text-muted">Bill #9012</small>
                            </td>
                            <td>Expenses</td>
                            <td><span class="badge bg-danger rounded-pill">Debit</span></td>
                            <td class="text-end fw-bold">$145.80</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Advanced DataTable Example -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Transaction History</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transactionModal">
                        <i class="fas fa-plus me-2"></i>New Transaction
                    </button>
                </div>
                <table id="transactionTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    
    <!-- Transaction Modal Form -->
    <div class="modal fade" id="transactionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header">
                    <h5 class="modal-title">New Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transactionForm" class="needs-validation" novalidate>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="transactionDate" required>
                                    <label for="transactionDate">Transaction Date</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="reference" required>
                                    <label for="reference">Reference Number</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="description" required>
                                    <label for="description">Description</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="account" required>
                                        <option value="">Select account</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank</option>
                                        <option value="receivable">Accounts Receivable</option>
                                    </select>
                                    <label for="account">Account</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="type" required>
                                        <option value="">Select type</option>
                                        <option value="debit">Debit</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                    <label for="type">Type</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="amount" step="0.01" required>
                                    <label for="amount">Amount</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="status" required>
                                        <option value="">Select status</option>
                                        <option value="pending">Pending</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <label for="status">Status</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="notes" style="height: 100px"></textarea>
                                    <label for="notes">Notes</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="transactionForm" class="btn btn-primary px-4">Save Transaction</button>
                </div>
            </div>
        </div>
    </div>

<?= $this->section('scripts') ?>
<!-- Add after existing scripts -->
<script>
$(document).ready(function() {
    // Initialize DataTable with sample data
    const table = $('#transactionTable').DataTable({
        data: [ // Sample data until API is ready
            {
                date: '2024-01-15',
                reference: 'INV-001',
                description: 'Office Supplies',
                notes: 'Monthly supplies',
                account: 'Expenses',
                type: 'debit',
                amount: 234.50,
                status: 'completed'
            },
            {
                date: '2024-01-14',
                reference: 'INV-002',
                description: 'Client Payment',
                notes: 'Project completion',
                account: 'Revenue',
                type: 'credit',
                amount: 1500.00,
                status: 'completed'
            }
            // Add more sample data as needed
        ],
        columns: [
            { 
                data: 'date',
                render: function(data) {
                    return moment(data).format('DD MMM YYYY');
                }
            },
            { data: 'reference' },
            { 
                data: 'description',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data}</div>
                            <small class="text-muted">${row.notes || ''}</small>`;
                }
            },
            { data: 'account' },
            { 
                data: 'type',
                render: function(data) {
                    return `<span class="badge bg-${data === 'debit' ? 'danger' : 'success'} rounded-pill">
                        ${data.charAt(0).toUpperCase() + data.slice(1)}
                    </span>`;
                }
            },
            { 
                data: 'amount',
                className: 'text-end',
                render: function(data) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(data);
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const badges = {
                        'pending': 'warning',
                        'completed': 'success',
                        'cancelled': 'secondary'
                    };
                    return `<span class="badge bg-${badges[data]}-subtle text-${badges[data]}">
                        ${data.charAt(0).toUpperCase() + data.slice(1)}
                    </span>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn btn-sm btn-light edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-light delete-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>`;
                }
            }
        ],
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

    // Form validation
    const form = document.getElementById('transactionForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        if (!form.checkValidity()) {
            event.stopPropagation();
        } else {
            // Handle form submission
            const formData = new FormData(form);
            // Add your API call here
            $('#transactionModal').modal('hide');
        }
        form.classList.add('was-validated');
    });
});
</script>
<?= $this->endSection() ?>
</script>
<?= $this->endSection() ?>