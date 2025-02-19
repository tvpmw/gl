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
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-wallet text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Assets</h6>
                        <h3 class="card-title mb-1">$1,234,567</h3>
                        <div class="small text-success">
                            <i class="fas fa-arrow-up"></i> 8.3% from last month
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
                        <i class="fas fa-list text-info fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Accounts</h6>
                        <h3 class="card-title mb-1">24</h3>
                        <div class="small text-muted">Active accounts</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Chart of Accounts</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#accountModal">
                    <i class="fas fa-plus me-1"></i>New Account
                </button>
            </div>
            <div class="table-responsive">
                <table id="accountsTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th>Parent Account</th>
                            <th class="text-end">Balance</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1000</td>
                            <td>
                                <div class="fw-bold">Cash</div>
                                <small class="text-muted">Current Assets</small>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary">Asset</span></td>
                            <td>-</td>
                            <td class="text-end fw-bold">$50,000.00</td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2000</td>
                            <td>
                                <div class="fw-bold">Accounts Payable</div>
                                <small class="text-muted">Current Liabilities</small>
                            </td>
                            <td><span class="badge bg-danger-subtle text-danger">Liability</span></td>
                            <td>-</td>
                            <td class="text-end fw-bold">$15,000.00</td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
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
</div>

<!-- Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title">New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="accountForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Account Code</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Type</label>
                        <select class="form-select" required>
                            <option value="">Select type</option>
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="revenue">Revenue</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Account</label>
                        <select class="form-select">
                            <option value="">No parent account</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="accountForm" class="btn btn-primary">Save Account</button>
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