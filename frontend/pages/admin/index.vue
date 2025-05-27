<template>
  <div class="dashboard">
    <!-- Top Statistic Cards -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-title">Revenue</div>
        <div class="stat-value">$13,456.5</div>
        <div class="stat-desc">Shipping fees are not included</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Orders</div>
        <div class="stat-value">53,668</div>
        <div class="stat-desc">Excluding orders in transit</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Products</div>
        <div class="stat-value">9,856</div>
        <div class="stat-desc">In 19 Categories</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Monthly Earning</div>
        <div class="stat-value">$6,982</div>
        <div class="stat-desc">Based on your local time.</div>
      </div>
    </div>

    <div class="main-content">
      <!-- Left Column -->
      <div class="main-left">
        <div class="card chart-card">
          <div class="card-title">Sale statistics</div>
          <div class="chart-placeholder">[Biểu đồ đường]</div>
        </div>
        <div class="card orders-card">
          <div class="card-title">Latest orders</div>
          <div class="table-placeholder">[Bảng đơn hàng]</div>
        </div>
      </div>
      <!-- Right Column -->
      <div class="main-right">
        <div class="card revenue-area-card">
          <div class="card-title">Revenue Base on Area</div>
          <div class="chart-placeholder">[Biểu đồ cột]</div>
        </div>
        <div class="card marketing-channel-card">
          <div class="card-title">Marketing Channel</div>
          <div class="progress-placeholder">[Progress bar]</div>
        </div>
        <div class="card new-members-card">
          <div class="card-title">New Members</div>
          <div class="members-placeholder">[Danh sách thành viên mới]</div>
        </div>
        <div class="card activities-card">
          <div class="card-title">Recent activities</div>
          <div class="activities-placeholder">[Hoạt động gần đây]</div>
        </div>
      </div>
    </div>

    <!-- Latest Orders Table (Full width, bottom) -->
    <div class="latest-orders-wrapper">
      <div class="card latest-orders-card">
        <div class="card-title">Latest orders</div>
        <div class="filters-row">
          <select class="filter-select">
            <option>All Categories</option>
          </select>
          <input type="date" class="filter-date" />
          <select class="filter-select">
            <option>Status</option>
          </select>
        </div>
        <div class="table-responsive">
          <table class="orders-table">
            <thead>
              <tr>
                <th><input type="checkbox" /></th>
                <th>Order ID</th>
                <th>Billing Name</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment Status</th>
                <th>Payment Method</th>
                <th>View Details</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="checkbox" /></td>
                <td class="order-link">#SK2540</td>
                <td>Neal Matthews</td>
                <td>07 Oct, 2022</td>
                <td>$400</td>
                <td><span class="badge paid">Paid</span></td>
                <td>Mastercard</td>
                <td><button class="view-btn">View details</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" /></td>
                <td class="order-link">#SK2541</td>
                <td>Jamal Burnett</td>
                <td>07 Oct, 2022</td>
                <td>$380</td>
                <td><span class="badge chargeback">Chargeback</span></td>
                <td>Visa</td>
                <td><button class="view-btn">View details</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" /></td>
                <td class="order-link">#SK2542</td>
                <td>Juan Mitchell</td>
                <td>06 Oct, 2022</td>
                <td>$384</td>
                <td><span class="badge paid">Paid</span></td>
                <td>Paypal</td>
                <td><button class="view-btn">View details</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" /></td>
                <td class="order-link">#SK2543</td>
                <td>Barry Dick</td>
                <td>05 Oct, 2022</td>
                <td>$412</td>
                <td><span class="badge paid">Paid</span></td>
                <td>Mastercard</td>
                <td><button class="view-btn">View details</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" /></td>
                <td class="order-link">#SK2544</td>
                <td>Ronald Taylor</td>
                <td>04 Oct, 2022</td>
                <td>$404</td>
                <td><span class="badge refund">Refund</span></td>
                <td>Visa</td>
                <td><button class="view-btn">View details</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" /></td>
                <td class="order-link">#SK2545</td>
                <td>Jacob Hunter</td>
                <td>04 Oct, 2022</td>
                <td>$392</td>
                <td><span class="badge paid">Paid</span></td>
                <td>Paypal</td>
                <td><button class="view-btn">View details</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="pagination-row">
          <button class="page-btn active">01</button>
          <button class="page-btn">02</button>
          <button class="page-btn">03</button>
          <span class="page-ellipsis">...</span>
          <button class="page-btn">16</button>
          <button class="page-btn">&gt;</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
})
</script>

<style scoped>
.dashboard {
  padding: 2rem 1rem;
  background: #f7fafd;
}

.stats-row {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
}

.stat-card {
  flex: 1;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  padding: 1.5rem 1.2rem;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.stat-title {
  font-size: 1rem;
  color: #888;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #222;
  margin-bottom: 0.5rem;
}

.stat-desc {
  font-size: 0.9rem;
  color: #aaa;
}

.main-content {
  display: flex;
  gap: 2rem;
}

.main-left {
  flex: 2;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.main-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  padding: 1.2rem 1rem;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.chart-placeholder,
.table-placeholder,
.progress-placeholder,
.members-placeholder,
.activities-placeholder {
  background: #f0f4f8;
  border-radius: 8px;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #bbb;
  font-size: 1rem;
}

.chart-card {
  min-height: 260px;
}

.orders-card {
  min-height: 220px;
}

.revenue-area-card {
  min-height: 180px;
}

.marketing-channel-card {
  min-height: 120px;
}

.new-members-card {
  min-height: 120px;
}

.activities-card {
  min-height: 120px;
}

.latest-orders-wrapper {
  margin-top: 2.5rem;
}

.latest-orders-card {
  padding: 2rem 1.5rem;
}

.filters-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.2rem;
}

.filter-select,
.filter-date {
  padding: 0.5rem 1rem;
  border: 1px solid #e0e6ed;
  border-radius: 6px;
  background: #f7fafd;
  color: #444;
  font-size: 1rem;
}

.table-responsive {
  overflow-x: auto;
}

.orders-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
}

.orders-table th,
.orders-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 1rem;
}

.orders-table th {
  background: #f7fafd;
  color: #888;
  font-weight: 600;
}

.orders-table tr {
  border-bottom: 1px solid #f0f4f8;
}

.order-link {
  color: #009688;
  font-weight: 600;
  cursor: pointer;
}

.badge {
  display: inline-block;
  padding: 0.3em 0.8em;
  border-radius: 6px;
  font-size: 0.95em;
  font-weight: 500;
}

.badge.paid {
  background: #e6f9f0;
  color: #1abc9c;
}

.badge.chargeback {
  background: #ffeaea;
  color: #e74c3c;
}

.badge.refund {
  background: #fff6e0;
  color: #f39c12;
}

.view-btn {
  background: #00796b;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 0.5em 1.2em;
  font-size: 1em;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.2s;
}

.view-btn:hover {
  background: #005a4f;
}

.pagination-row {
  display: flex;
  gap: 0.5rem;
  margin-top: 1.5rem;
  align-items: center;
}

.page-btn {
  background: #e0f2f1;
  color: #00796b;
  border: none;
  border-radius: 5px;
  padding: 0.4em 1em;
  font-size: 1em;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.2s;
}

.page-btn.active,
.page-btn:hover {
  background: #00796b;
  color: #fff;
}

.page-ellipsis {
  color: #bbb;
  font-size: 1.1em;
  padding: 0 0.5em;
}
</style>