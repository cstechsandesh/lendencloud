<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chart of Accounts</h4>
        <p class="text-muted small mb-0">Double-entry general ledger chart structured by Assets, Liabilities, Equity, Revenue, and Expenses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('accounts/groups') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-diagram-3 me-1"></i>Account Groups
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            <i class="bi bi-plus-lg me-1"></i>New Account
        </button>
    </div>
</div>

<div class="accordion mb-4" id="accountsAccordion">
    <?php
    $typeNames = [
        'asset'     => ['title' => 'Assets (1000 - 1999)', 'badge' => 'bg-info text-dark', 'icon' => 'bi-wallet2'],
        'liability' => ['title' => 'Liabilities (2000 - 2999)', 'badge' => 'bg-warning text-dark', 'icon' => 'bi-shield-exclamation'],
        'equity'    => ['title' => 'Equity & Capital (3000 - 3999)', 'badge' => 'bg-primary', 'icon' => 'bi-bank'],
        'revenue'   => ['title' => 'Revenue & Income (4000 - 4999)', 'badge' => 'bg-success', 'icon' => 'bi-graph-up'],
        'expense'   => ['title' => 'Expenses & COGS (5000 - 5999)', 'badge' => 'bg-danger', 'icon' => 'bi-graph-down'],
    ];

    $idx = 0;
    foreach ($typeNames as $typeKey => $meta):
        $accList = $groupedAccounts[$typeKey] ?? [];
        $idx++;
    ?>
    <div class="accordion-item border mb-3 rounded-3 overflow-hidden shadow-sm">
        <h2 class="accordion-header" id="heading<?= $idx ?>">
            <button class="accordion-button <?= $idx > 1 ? 'collapsed' : '' ?> fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $idx ?>">
                <i class="bi <?= $meta['icon'] ?> fs-5 me-2 text-primary"></i>
                <span class="me-2"><?= $meta['title'] ?></span>
                <span class="badge <?= $meta['badge'] ?> rounded-pill px-2"><?= count($accList) ?> Accounts</span>
            </button>
        </h2>
        <div id="collapse<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 1 ? 'show' : '' ?>" data-bs-parent="#accountsAccordion">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 120px;">Code</th>
                                <th>Account Name</th>
                                <th>Sub-Group</th>
                                <th>Normal Balance</th>
                                <th class="text-end">Current Balance</th>
                                <th class="text-center" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accList)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No accounts in this category.</td></tr>
                            <?php else: ?>
                                <?php foreach ($accList as $acc): ?>
                                    <tr>
                                        <td><code class="fw-bold fs-6 text-dark"><?= esc($acc['account_code']) ?></code></td>
                                        <td>
                                            <a href="<?= base_url('accounts/ledger/' . $acc['account_id']) ?>" class="fw-semibold text-decoration-none">
                                                <?= esc($acc['account_name']) ?>
                                            </a>
                                            <?php if ($acc['account_is_system']): ?>
                                                <span class="badge bg-light text-secondary border ms-1" style="font-size:0.7rem;">System Core</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($acc['group_name'] ?? '—') ?></td>
                                        <td><span class="badge bg-light text-secondary border"><?= strtoupper($acc['account_balance_type']) ?></span></td>
                                        <td class="text-end fw-bold">
                                            <?= esc($currency_symbol) ?> <?= number_format($acc['account_current_balance'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('accounts/ledger/' . $acc['account_id']) ?>" class="btn btn-outline-primary" title="View General Ledger">
                                                    <i class="bi bi-book me-1"></i>Ledger
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                                    data-bs-toggle="modal" data-bs-target="#editAccountModal"
                                                    data-action="<?= base_url('accounts/update/' . $acc['account_id']) ?>"
                                                    data-item='<?= json_encode($acc) ?>'
                                                    title="Edit Account">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if (!$acc['account_is_system']): ?>
                                                    <a href="<?= base_url('accounts/delete/' . $acc['account_id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this account?');" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal: Add New Account -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-plus-circle text-primary me-2"></i>Add New Ledger Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('accounts/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-5">
                            <label class="form-label small fw-semibold">Account Code <span class="text-danger">*</span></label>
                            <input type="text" name="account_code" class="form-control" placeholder="e.g. 1025" required>
                        </div>
                        <div class="col-sm-7">
                            <label class="form-label small fw-semibold">Category Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select" required>
                                <option value="asset">Asset (DR Normal)</option>
                                <option value="liability">Liability (CR Normal)</option>
                                <option value="equity">Equity / Capital (CR Normal)</option>
                                <option value="revenue">Revenue / Income (CR Normal)</option>
                                <option value="expense">Expense / Cost (DR Normal)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Account Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" placeholder="e.g. ICICI Bank Operating A/C" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Sub-Group</label>
                            <select name="account_group_id" class="form-select">
                                <option value="">— Primary Level —</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['group_id'] ?>"><?= esc($g['group_name']) ?> (<?= ucfirst($g['group_type']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Normal Balance <span class="text-danger">*</span></label>
                            <select name="account_balance_type" class="form-select" required>
                                <option value="debit">Debit (DR)</option>
                                <option value="credit">Credit (CR)</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Opening Balance</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= esc($currency_symbol) ?></span>
                                <input type="number" step="0.01" name="account_opening_balance" class="form-control" value="0.00">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description / Remarks</label>
                            <textarea name="account_description" class="form-control" rows="2" placeholder="Account purpose, bank account number, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Ledger Account -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Ledger Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAccountForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-5">
                            <label class="form-label small fw-semibold">Account Code</label>
                            <input type="text" name="account_code" class="form-control" required>
                        </div>
                        <div class="col-sm-7">
                            <label class="form-label small fw-semibold">Category Type</label>
                            <select name="account_type" class="form-select" required>
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity</option>
                                <option value="revenue">Revenue</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Account Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Sub-Group</label>
                            <select name="account_group_id" class="form-select">
                                <option value="">— Primary Level —</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['group_id'] ?>"><?= esc($g['group_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Normal Balance</label>
                            <select name="account_balance_type" class="form-select" required>
                                <option value="debit">Debit (DR)</option>
                                <option value="credit">Credit (CR)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description / Remarks</label>
                            <textarea name="account_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>