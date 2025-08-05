<template>
  <div class="shipping-calculator-page">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <!-- Header -->
          <div class="text-center mb-5">
            <h1 class="display-6 fw-bold text-primary">
              <i class="fas fa-calculator me-3"></i>
              Tính phí vận chuyển
            </h1>
            <p class="lead text-muted">
              Tính toán phí vận chuyển chính xác với Giao Hàng Nhanh (GHN)
            </p>
          </div>

          <!-- Shipping Calculator Component -->
          <ShippingCalculator />

          <!-- Thông tin bổ sung -->
          <div class="row mt-5">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Thông tin dịch vụ
                  </h6>
                </div>
                <div class="card-body">
                  <ul class="list-unstyled">
                    <li class="mb-2">
                      <i class="fas fa-check text-success me-2"></i>
                      <strong>Hàng nhẹ:</strong> Sử dụng kích thước và cân nặng tổng
                    </li>
                    <li class="mb-2">
                      <i class="fas fa-check text-success me-2"></i>
                      <strong>Hàng nặng:</strong> Tính theo từng sản phẩm riêng biệt
                    </li>
                    <li class="mb-2">
                      <i class="fas fa-check text-success me-2"></i>
                      <strong>Giới hạn:</strong> Tối đa 1.600.000 gram
                    </li>
                    <li class="mb-2">
                      <i class="fas fa-check text-success me-2"></i>
                      <strong>Kích thước:</strong> Tối đa 200cm x 200cm x 200cm
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Thời gian giao hàng
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-6">
                      <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-primary mb-1">Nội thành</h6>
                        <p class="mb-0 text-muted">1-2 ngày</p>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-primary mb-1">Tỉnh lân cận</h6>
                        <p class="mb-0 text-muted">2-4 ngày</p>
                      </div>
                    </div>
                    <div class="col-6 mt-3">
                      <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-primary mb-1">Toàn quốc</h6>
                        <p class="mb-0 text-muted">3-7 ngày</p>
                      </div>
                    </div>
                    <div class="col-6 mt-3">
                      <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-primary mb-1">Vùng xa</h6>
                        <p class="mb-0 text-muted">5-10 ngày</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- FAQ Section -->
          <div class="mt-5">
            <div class="card">
              <div class="card-header">
                <h6 class="card-title mb-0">
                  <i class="fas fa-question-circle me-2"></i>
                  Câu hỏi thường gặp
                </h6>
              </div>
              <div class="card-body">
                <div class="accordion" id="shippingFAQ">
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        Phí vận chuyển được tính như thế nào?
                      </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#shippingFAQ">
                      <div class="accordion-body">
                        Phí vận chuyển được tính dựa trên khoảng cách, cân nặng, kích thước và loại dịch vụ. 
                        Hàng nhẹ tính theo tổng kích thước, hàng nặng tính theo từng sản phẩm riêng biệt.
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Có thể giao hàng đến đâu?
                      </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#shippingFAQ">
                      <div class="accordion-body">
                        GHN cung cấp dịch vụ giao hàng toàn quốc, bao gồm 63 tỉnh thành. 
                        Một số vùng xa có thể có phụ phí vận chuyển.
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        Thời gian giao hàng là bao lâu?
                      </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#shippingFAQ">
                      <div class="accordion-body">
                        Thời gian giao hàng từ 1-10 ngày tùy thuộc vào khoảng cách. 
                        Nội thành: 1-2 ngày, tỉnh lân cận: 2-4 ngày, toàn quốc: 3-7 ngày.
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
</template>

<script setup>
import ShippingCalculator from '../components/shipping/ShippingCalculator.vue';
</script>

<style scoped>
.shipping-calculator-page {
  padding: 2rem 0;
  background-color: #f8f9fc;
  min-height: 100vh;
}

.display-6 {
  font-size: 2.5rem;
}

.card {
  border: 1px solid #e3e6f0;
  border-radius: 0.35rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
  background-color: #f8f9fc;
  border-bottom: 1px solid #e3e6f0;
  padding: 1rem 1.25rem;
}

.card-title {
  color: #5a5c69;
  font-weight: 700;
}

.accordion-button:not(.collapsed) {
  background-color: #e7f1ff;
  color: #0c63e4;
}

.accordion-button:focus {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.bg-light {
  background-color: #f8f9fc !important;
}
</style> 