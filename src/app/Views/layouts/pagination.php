<?php
if (
    !isset($pagination)
    || !is_array($pagination)
    || (int) ($pagination['total_pages'] ?? 0) <= 1
) {
    return;
}

$paginationPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$paginationPath = is_string($paginationPath) ? $paginationPath : '/';
$paginationQuery = $_GET;
$paginationHref = static function (int $page) use ($paginationPath, $paginationQuery): string {
    $query = $paginationQuery;
    $query['page'] = $page;

    return $paginationPath . '?' . http_build_query($query);
};
?>
<nav aria-label="Điều hướng trang" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
            <a
                class="page-link"
                href="<?= htmlspecialchars($paginationHref(max(1, $pagination['current_page'] - 1)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                aria-label="Trang trước"
            >Trước</a>
        </li>
        <?php for ($pageNumber = 1; $pageNumber <= $pagination['total_pages']; $pageNumber++): ?>
            <li class="page-item <?= $pagination['current_page'] === $pageNumber ? 'active' : '' ?>">
                <a
                    class="page-link"
                    href="<?= htmlspecialchars($paginationHref($pageNumber), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    <?= $pagination['current_page'] === $pageNumber ? 'aria-current="page"' : '' ?>
                ><?= $pageNumber ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
            <a
                class="page-link"
                href="<?= htmlspecialchars($paginationHref(min($pagination['total_pages'], $pagination['current_page'] + 1)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                aria-label="Trang sau"
            >Sau</a>
        </li>
    </ul>
</nav>
