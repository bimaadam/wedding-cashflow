<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

$pageTitle = 'Cash Flow - Graceful Decoration';
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-sm-12">
    <div class="home-tab">
      <div class="d-sm-flex align-items-center justify-content-between border-bottom">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active ps-0" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="transactions-tab" data-bs-toggle="tab" href="#transactions" role="tab" aria-selected="false">Transactions</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="analytics-tab" data-bs-toggle="tab" href="#analytics" role="tab" aria-selected="false">Analytics</a>
          </li>
          <li class="nav-item">
            <a class="nav-link border-0" id="reports-tab" data-bs-toggle="tab" href="#reports" role="tab" aria-selected="false">Reports</a>
          </li>
        </ul>
        <div>
          <div class="btn-wrapper">
            <a href="pemasukan/" class="btn btn-outline-dark align-items-center"><i class="icon-plus"></i> Add Income</a>
            <a href="pengeluaran.php" class="btn btn-outline-dark"><i class="icon-minus"></i> Add Expense</a>
            <a href="#" class="btn btn-primary text-white me-0"><i class="icon-download"></i> Export</a>
          </div>
        </div>
      </div>
      <div class="tab-content tab-content-basic">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
          <div class="row">
            <div class="col-sm-12">
              <div class="statistics-details d-flex align-items-center justify-content-between">
                <div>
                  <p class="statistics-title">Total Income</p>
                  <h3 class="rate-percentage">Rp 125,000,000</h3>
                  <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>+15.3%</span></p>
                </div>
                <div>
                  <p class="statistics-title">Total Expenses</p>
                  <h3 class="rate-percentage">Rp 87,500,000</h3>
                  <p class="text-danger d-flex"><i class="mdi mdi-menu-down"></i><span>-2.1%</span></p>
                </div>
                <div>
                  <p class="statistics-title">Net Cash Flow</p>
                  <h3 class="rate-percentage">Rp 37,500,000</h3>
                  <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>+25.8%</span></p>
                </div>
                <div class="d-none d-md-block">
                  <p class="statistics-title">Profit Margin</p>
                  <h3 class="rate-percentage">30%</h3>
                  <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>+5.2%</span></p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-lg-8 d-flex flex-column">
              <div class="row flex-grow">
                <div class="col-12 grid-margin stretch-card">
                  <div class="card card-rounded">
                    <div class="card-body">
                      <div class="d-sm-flex justify-content-between align-items-start">
                        <div>
                          <h4 class="card-title card-title-dash">Cash Flow Trend</h4>
                          <h5 class="card-subtitle card-subtitle-dash">Monthly income vs expenses comparison</h5>
                        </div>
                        <div id="performance-line-legend"></div>
                      </div>
                      <div class="chartjs-wrapper mt-5">
                        <canvas id="cashflowChart"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 d-flex flex-column">
              <div class="row flex-grow">
                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                  <div class="card bg-primary card-rounded">
                    <div class="card-body pb-0">
                      <h4 class="card-title card-title-dash text-white mb-4">Monthly Summary</h4>
                      <div class="row">
                        <div class="col-sm-4">
                          <p class="status-summary-ight-white mb-1">This Month</p>
                          <h2 class="text-info">Rp 12.5M</h2>
                        </div>
                        <div class="col-sm-8">
                          <div class="status-summary-chart-wrapper pb-4">
                            <canvas id="status-summary"></canvas>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                  <div class="card card-rounded">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-sm-6">
                          <div class="d-flex justify-content-between align-items-center mb-2 d-sm-block">
                            <h6 class="mb-0">Income Growth</h6>
                            <p class="text-success">+15.3%</p>
                          </div>
                          <h4 class="mb-0">Rp 25,000,000</h4>
                        </div>
                        <div class="col-sm-6">
                          <div class="d-flex justify-content-between align-items-center mb-2 d-sm-block">
                            <h6 class="mb-0">Expense Control</h6>
                            <p class="text-success">-2.1%</p>
                          </div>
                          <h4 class="mb-0">Rp 17,500,000</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Transactions Tab -->
        <div class="tab-pane fade" id="transactions" role="tabpanel" aria-labelledby="transactions">
          <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Recent Transactions</h4>
                      <p class="card-subtitle card-subtitle-dash">Latest income and expense records</p>
                    </div>
                    <div>
                      <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle toggle-dark btn-lg mb-0 me-0" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Filter </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                          <h6 class="dropdown-header">Filter by</h6>
                          <a class="dropdown-item" href="#">All Transactions</a>
                          <a class="dropdown-item" href="#">Income Only</a>
                          <a class="dropdown-item" href="#">Expenses Only</a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item" href="#">This Month</a>
                          <a class="dropdown-item" href="#">Last Month</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive mt-1">
                    <table class="table select-table">
                      <thead>
                        <tr>
                          <th>
                            <div class="form-check form-check-flat mt-0">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" aria-checked="false"><i class="input-helper"></i></label>
                            </div>
                          </th>
                          <th>Date</th>
                          <th>Description</th>
                          <th>Category</th>
                          <th>Type</th>
                          <th>Amount</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <div class="form-check form-check-flat mt-0">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" aria-checked="false"><i class="input-helper"></i></label>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex ">
                              <div>
                                <h6>Jan 15, 2024</h6>
                                <p>10:30 AM</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <h6>Wedding Event - Sarah & John</h6>
                            <p>Full decoration package</p>
                          </td>
                          <td>
                            <div class="badge badge-outline-secondary">Wedding Services</div>
                          </td>
                          <td>
                            <div class="badge badge-outline-success">Income</div>
                          </td>
                          <td>
                            <h6>Rp 25,000,000</h6>
                          </td>
                          <td>
                            <div class="badge badge-outline-success">Completed</div>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                <a class="dropdown-item" href="#"><i class="mdi mdi-eye"></i> View</a>
                                <a class="dropdown-item" href="#"><i class="mdi mdi-pencil"></i> Edit</a>
                                <a class="dropdown-item" href="#"><i class="mdi mdi-delete"></i> Delete</a>
                              </div>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="form-check form-check-flat mt-0">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" aria-checked="false"><i class="input-helper"></i></label>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex">
                              <div>
                                <h6>Jan 14, 2024</h6>
                                <p>02:15 PM</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <h6>Decoration Materials</h6>
                            <p>Flowers, fabrics, lighting</p>
                          </td>
                          <td>
                            <div class="badge badge-outline-secondary">Materials</div>
                          </td>
                          <td>
                            <div class="badge badge-outline-danger">Expense</div>
                          </td>
                          <td>
                            <h6>Rp 5,500,000</h6>
                          </td>
                          <td>
                            <div class="badge badge-outline-success">Paid</div>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                <a class="dropdown-item" href="#"><i class="mdi mdi-eye"></i> View</a>
                                <a class="dropdown-item" href="#"><i class="mdi mdi-pencil"></i> Edit</a>
                                <a class="dropdown-item" href="#"><i class="mdi mdi-delete"></i> Delete</a>
                              </div>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="form-check form-check-flat mt-0">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" aria-checked="false"><i class="input-helper"></i></label>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex">
                              <div>
                                <h6>Jan 12, 2024</h6>
                                <p>11:45 AM</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <h6>Wedding Event - Mike & Lisa</h6>
                            <p>Outdoor decoration setup</p>
                          </td>
                          <td>
                            <div class="badge badge-outline-secondary">Wedding Services</div>
                          </td>
                          <td>
                            <div class="badge badge-outline-success">Income</div>
                          </td>
                          <td>
                            <h6>Rp 18,000,000</h6>
                          </td>
                          <td>
                            <div class="badge badge-outline-warning">Pending</div>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton6">
                                <a class="dropdown-item" href="#"><i class="mdi mdi-eye"></i> View</a>
                                <a class="dropdown-item" href="#"><i class="mdi mdi-pencil"></i> Edit</a>
                                <a class="dropdown-item" href="#"><i class="mdi mdi-delete"></i> Delete</a>
                              </div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Analytics Tab -->
        <div class="tab-pane fade" id="analytics" role="tabpanel" aria-labelledby="analytics">
          <div class="row">
            <div class="col-lg-6 grid-margin stretch-card">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Income Categories</h4>
                      <p class="card-subtitle card-subtitle-dash">Revenue breakdown by service type</p>
                    </div>
                  </div>
                  <div class="chartjs-wrapper mt-4">
                    <canvas id="incomeChart" width="400" height="200"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6 grid-margin stretch-card">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Expense Categories</h4>
                      <p class="card-subtitle card-subtitle-dash">Cost breakdown by category</p>
                    </div>
                  </div>
                  <div class="chartjs-wrapper mt-4">
                    <canvas id="expenseChart" width="400" height="200"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Reports Tab -->
        <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports">
          <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
              <div class="card card-rounded">
                <div class="card-body">
                  <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                      <h4 class="card-title card-title-dash">Financial Reports</h4>
                      <p class="card-subtitle card-subtitle-dash">Generate and download financial reports</p>
                    </div>
                  </div>
                  
                  <div class="row mt-4">
                    <div class="col-md-4">
                      <div class="card bg-gradient-primary text-white">
                        <div class="card-body">
                          <h5>Monthly Report</h5>
                          <p>Detailed monthly financial summary</p>
                          <button class="btn btn-light btn-sm">Generate Report</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="card bg-gradient-success text-white">
                        <div class="card-body">
                          <h5>Quarterly Report</h5>
                          <p>Quarterly business performance analysis</p>
                          <button class="btn btn-light btn-sm">Generate Report</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="card bg-gradient-info text-white">
                        <div class="card-body">
                          <h5>Annual Report</h5>
                          <p>Comprehensive yearly financial report</p>
                          <button class="btn btn-light btn-sm">Generate Report</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>