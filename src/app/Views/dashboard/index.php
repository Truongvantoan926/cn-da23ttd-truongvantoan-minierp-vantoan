<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="mb-4">
        <h1 class="h3 mb-2">
            Xin chào, <?= htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!
        </h1>
        <p class="text-secondary mb-0">Tổng quan hoạt động doanh nghiệp</p>
    </div>

    <div class="row g-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-secondary mb-2">Tổng nhân viên</p>
                        <p class="h3 mb-0">--</p>
                    </div>
                    <span class="rounded-circle bg-primary-subtle text-primary p-3">
                        <i class="fa-solid fa-users fa-lg" aria-hidden="true"></i>
                    </span>
                </div>
            </section>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-secondary mb-2">Tổng sản phẩm</p>
                        <p class="h3 mb-0">--</p>
                    </div>
                    <span class="rounded-circle bg-success-subtle text-success p-3">
                        <i class="fa-solid fa-boxes-stacked fa-lg" aria-hidden="true"></i>
                    </span>
                </div>
            </section>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-secondary mb-2">Đơn hàng mới</p>
                        <p class="h3 mb-0">--</p>
                    </div>
                    <span class="rounded-circle bg-warning-subtle text-warning p-3">
                        <i class="fa-solid fa-cart-shopping fa-lg" aria-hidden="true"></i>
                    </span>
                </div>
            </section>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-secondary mb-2">Doanh thu</p>
                        <p class="h3 mb-0">--</p>
                    </div>
                    <span class="rounded-circle bg-info-subtle text-info p-3">
                        <i class="fa-solid fa-chart-line fa-lg" aria-hidden="true"></i>
                    </span>
                </div>
            </section>
        </div>
    </div>
</main>
