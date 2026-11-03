<?= $this->extend('landing/layout') ?>

<?= $this->section('content') ?>
<!-- Subpage Hero -->
<section class="subpage-hero text-center text-lg-start">
    <div class="container">
        <?= seo_breadcrumbs_html($breadcrumbs ?? []) ?>
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <span class="hero-badge mb-3">
                    <i class="bi bi-grid-3x3-gap-fill text-primary"></i> Modular ERP Architecture
                </span>
                <h1 class="display-5 fw-extrabold text-white mb-3" style="font-weight: 800;">
                    Complete Core Business &amp; Financial Modules
                </h1>
                <p class="lead text-white-50 mb-0" style="max-width: 680px;">
                    Every module in LenDen Cloud is built to adhere to formal double-entry accounting standards and Indian GST regulations, providing your company with complete operational clarity.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <a href="<?= base_url('subscribe') ?>" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow-lg">
                    <i class="bi bi-rocket-takeoff-fill me-1"></i> Try All Modules Free
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Modules Deep Dive -->
<section class="py-5">
    <div class="container py-4">
        
        <!-- Module 1: Sales & Invoicing -->
        <div class="row align-items-center g-5 mb-5 pb-4 border-bottom" id="sales">
            <div class="col-12 col-lg-6">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-4 bg-primary-subtle text-primary fs-3 mb-3">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Sales Orders &amp; GST Invoicing</h2>
                <p class="text-muted mb-4">
                    Accelerate your sales pipeline from quotation to payment collection. LenDen provides automated GST calculation with full support for intra-state (CGST + SGST) and inter-state (IGST) transactions.
                </p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Quotations &amp; Orders</strong>
                                <small class="text-muted">Convert sales orders to tax invoices in 1 click.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">GST Slabs &amp; HSN Codes</strong>
                                <small class="text-muted">0%, 5%, 12%, 18%, and 28% preconfigured.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Print-Ready PDF Invoices</strong>
                                <small class="text-muted">Clean thermal &amp; A4 invoice formats.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Customer Payment Vouchers</strong>
                                <small class="text-muted">Direct reconciliation against open invoices.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm p-4 bg-light rounded-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="fw-bold text-dark"><i class="bi bi-file-earmark-text text-primary me-2"></i>Tax Invoice #INV-2026-0042</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Paid &amp; Reconciled</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Billed To: Supreme Enterprises (27AABC1234M1Z8)</span>
                        <span>Date: 02 Nov 2026</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-3">
                            <thead class="table-light small">
                                <tr><th>Item / SKU</th><th>Qty</th><th>Rate</th><th>GST</th><th class="text-end">Total</th></tr>
                            </thead>
                            <tbody class="small">
                                <tr><td>Industrial Motor 5HP</td><td>2</td><td>₹18,500</td><td>18%</td><td class="text-end">₹43,660.00</td></tr>
                                <tr><td>High-Temp Sealant</td><td>10</td><td>₹450</td><td>18%</td><td class="text-end">₹5,310.00</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="fw-bold text-dark">Invoice Total (Incl. GST):</span>
                        <span class="fs-5 fw-bold text-primary">₹48,970.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module 2: Double-Entry Ledgers -->
        <div class="row align-items-center g-5 mb-5 pb-4 border-bottom flex-lg-row-reverse" id="accounting">
            <div class="col-12 col-lg-6">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-4 bg-success-subtle text-success fs-3 mb-3">
                    <i class="bi bi-journal-check"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Full Double-Entry Ledgers &amp; Chart of Accounts</h2>
                <p class="text-muted mb-4">
                    Audit-proof accounting that guarantees mathematically balanced books. Every commercial action automatically creates dual-legged ledger records across Assets, Liabilities, Equity, Revenue, and Expenses.
                </p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Hierarchical Chart of Accounts</strong>
                                <small class="text-muted">Standard 5 account types with infinite groups.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Journal &amp; Contra Vouchers</strong>
                                <small class="text-muted">Manual journal adjustments &amp; bank-cash contra.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Instant General Ledger</strong>
                                <small class="text-muted">Filter any account by date range and drill-down.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Automatic Balance Verification</strong>
                                <small class="text-muted">Total Debits must equal Total Credits before posting.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm p-4 bg-light rounded-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="fw-bold text-dark"><i class="bi bi-journal-text text-success me-2"></i>Journal Entry #JRN-2026-012</span>
                        <span class="badge bg-success">Balanced</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-2">
                            <thead class="table-light small">
                                <tr><th>Account Ledger</th><th class="text-end">Debit (₹)</th><th class="text-end">Credit (₹)</th></tr>
                            </thead>
                            <tbody class="small">
                                <tr><td>Bank Account (HDFC)</td><td class="text-end fw-semibold">50,000.00</td><td class="text-end text-muted">-</td></tr>
                                <tr><td>Accounts Receivable (Sundry Debtors)</td><td class="text-end text-muted">-</td><td class="text-end fw-semibold">50,000.00</td></tr>
                            </tbody>
                            <tfoot class="table-light fw-bold small">
                                <tr><td>Totals</td><td class="text-end text-success">₹50,000.00</td><td class="text-end text-success">₹50,000.00</td></tr>
                            </tfoot>
                        </table>
                    </div>
                    <small class="text-muted d-block text-center mt-2">Trial balance delta: ₹0.00 (Perfect parity)</small>
                </div>
            </div>
        </div>

        <!-- Module 3: Inventory & Warehouse -->
        <div class="row align-items-center g-5 mb-5 pb-4 border-bottom" id="inventory">
            <div class="col-12 col-lg-6">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-4 bg-info-subtle text-info fs-3 mb-3">
                    <i class="bi bi-boxes"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Multi-Warehouse Inventory &amp; Stock Tracking</h2>
                <p class="text-muted mb-4">
                    Monitor product inventory levels in real-time across your main warehouse, factory store, or branch retail hubs. Prevent stockouts and record accurate stock valuation directly into ledgers.
                </p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Multi-Warehouse Storage</strong>
                                <small class="text-muted">Partition stock per warehouse or branch facility.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Stock Adjustments &amp; Audits</strong>
                                <small class="text-muted">Record damages, variances, and cycle count reconciliation.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Automated Cost Valuation</strong>
                                <small class="text-muted">Real-time stock valuation reflects in Balance Sheet.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Categories &amp; Units of Measure</strong>
                                <small class="text-muted">Custom units: Pieces, Kg, Meters, Liters, Boxes.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm p-4 bg-light rounded-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="fw-bold text-dark"><i class="bi bi-box-seam text-info me-2"></i>Live Stock Matrix by Warehouse</span>
                        <span class="badge bg-primary">3 Warehouses</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-2">
                            <thead class="table-light small">
                                <tr><th>Product SKU</th><th>Main Depot</th><th>Pune Hub</th><th>Total Units</th></tr>
                            </thead>
                            <tbody class="small">
                                <tr><td>Steel Fasteners M8</td><td>1,200 pcs</td><td>450 pcs</td><td class="fw-bold">1,650 pcs</td></tr>
                                <tr><td>Hydraulic Valve 200L</td><td>18 pcs</td><td>4 pcs</td><td class="fw-bold">22 pcs</td></tr>
                                <tr><td>Copper Wire Spool 100m</td><td>95 spools</td><td>30 spools</td><td class="fw-bold">125 spools</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module 4: Purchases & Vendor Bills -->
        <div class="row align-items-center g-5 mb-5 pb-4 border-bottom flex-lg-row-reverse" id="purchases">
            <div class="col-12 col-lg-6">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-4 bg-warning-subtle text-warning fs-3 mb-3">
                    <i class="bi bi-truck"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Purchases &amp; Vendor Bill Matching</h2>
                <p class="text-muted mb-4">
                    Maintain rigorous control over procurement expenditure. Create purchase orders, match against vendor invoices, update stock, and disburse payment vouchers with full input tax credit (ITC) audit trails.
                </p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Purchase Orders (PO)</strong>
                                <small class="text-muted">Issue POs to suppliers with pre-negotiated terms.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Vendor Bills &amp; ITC</strong>
                                <small class="text-muted">Track GST Input Tax Credit claims for returns filing.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Supplier Payment Vouchers</strong>
                                <small class="text-muted">Bank transfer, Cheque, and Cash voucher payouts.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Payables Aging Analysis</strong>
                                <small class="text-muted">Monitor due dates to prevent supplier delays.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm p-4 bg-light rounded-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="fw-bold text-dark"><i class="bi bi-receipt text-warning me-2"></i>Vendor Bill #BILL-2026-0089</span>
                        <span class="badge bg-warning text-dark">Awaiting Payment</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Vendor: Precision Steel Mills</span>
                        <span>Due: 15 Nov 2026</span>
                    </div>
                    <div class="p-3 bg-white rounded-3 border mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Subtotal (Net):</span>
                            <span>₹1,12,000.00</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Input GST (18% IGST):</span>
                            <span>₹20,160.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold pt-2 border-top">
                            <span>Bill Payable:</span>
                            <span class="text-danger">₹1,32,160.00</span>
                        </div>
                    </div>
                    <small class="text-muted d-block">Automatic debit to Inventory Asset &bull; Credit to Sundry Creditors</small>
                </div>
            </div>
        </div>

        <!-- Module 5: Real-Time Reports -->
        <div class="row align-items-center g-5" id="reports">
            <div class="col-12 col-lg-6">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-4 bg-danger-subtle text-danger fs-3 mb-3">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Real-Time Financial Statements &amp; Audit Reports</h2>
                <p class="text-muted mb-4">
                    Eliminate month-end closing bottlenecks. Since every voucher and invoice posts immediately to underlying ledgers, your management reports and statutory statements are continuously up to the second.
                </p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Balance Sheet</strong>
                                <small class="text-muted">Standard Assets vs. Liabilities &amp; Owner's Equity.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Profit &amp; Loss (P&amp;L)</strong>
                                <small class="text-muted">Gross Profit, Operating Expenses &amp; Net Margin.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Trial Balance</strong>
                                <small class="text-muted">Complete audit trail across every active account ledger.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div>
                                <strong class="d-block text-dark small">Day Book &amp; Cash Flow</strong>
                                <small class="text-muted">Chronological transaction record for tax audits.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm p-4 bg-light rounded-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="fw-bold text-dark"><i class="bi bi-bar-chart-fill text-danger me-2"></i>Executive P&amp;L Snapshot</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Q3 FY26-27</span>
                    </div>
                    <div class="p-3 bg-white rounded-3 border mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Operating Sales Revenue:</span>
                            <span class="text-success fw-bold">₹28,40,000.00</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Cost of Goods Sold (COGS):</span>
                            <span class="text-muted">₹14,20,000.00</span>
                        </div>
                        <div class="d-flex justify-content-between small fw-semibold pt-1 border-top">
                            <span>Gross Margin:</span>
                            <span class="text-dark">₹14,20,000.00 (50.0%)</span>
                        </div>
                        <div class="d-flex justify-content-between small mt-2 mb-1">
                            <span>Administrative &amp; General Expenses:</span>
                            <span class="text-muted">₹3,45,000.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold pt-2 border-top mt-1">
                            <span class="text-dark">Net Operating Income:</span>
                            <span class="text-primary fs-6">₹10,75,000.00</span>
                        </div>
                    </div>
                    <small class="text-muted d-block text-center">Exportable to CSV, Excel, and Audit PDF</small>
                </div>
            </div>
        </div>

    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('schema') ?>
<?= seo_schema('breadcrumbs', ['items' => $breadcrumbs ?? []]) ?>
<?= seo_schema('webpage', [
    'title'       => 'Core Modules & Accounting Capabilities — LenDen Cloud',
    'description' => 'Explore LenDen Cloud\'s core modules: automated GST tax invoicing, formal double-entry general ledgers, multi-warehouse stock management, and real-time financial reporting.',
    'url'         => seo_canonical_url('features'),
]) ?>
<?= $this->endSection() ?>

