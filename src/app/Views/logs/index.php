<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Nhật ký hệ thống</h1>
            <p class="text-secondary mb-0">Theo dõi nhật ký hoạt động trong hệ thống</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Thời gian</th>
                        <th scope="col">Người thực hiện</th>
                        <th scope="col">Hành động</th>
                        <th scope="col">Mô tả chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">
                                Chưa có hoạt động nào được ghi nhận.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <?= htmlspecialchars((string) $log['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) $log['user_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border">
                                        <?= htmlspecialchars((string) $log['action'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-break">
                                    <?= htmlspecialchars((string) $log['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php require $paginationViewPath; ?>
</main>
