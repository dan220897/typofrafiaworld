<?php
// admin/reviews.php - Управление отзывами
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Review.php';

// Проверяем авторизацию и права
checkAdminAuth('view_reviews');

// Получаем параметры фильтрации
$status = $_GET['status'] ?? 'all';
$rating = $_GET['rating'] ?? '';
$has_reply = $_GET['has_reply'] ?? '';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$review = new Review($db);

// Получаем отзывы
$filters = [];
if ($status === 'published') {
    $filters['is_published'] = 1;
} elseif ($status === 'unpublished') {
    $filters['is_published'] = 0;
}
if ($rating) {
    $filters['rating'] = $rating;
}
if ($has_reply !== '') {
    $filters['has_reply'] = $has_reply;
}
if ($search) {
    $filters['search'] = $search;
}
if ($date_from) {
    $filters['date_from'] = $date_from;
}
if ($date_to) {
    $filters['date_to'] = $date_to;
}

$offset = ($page - 1) * $per_page;
$reviews = $review->getReviews($filters, $per_page, $offset);
$total_reviews = $review->getReviewsCount($filters);
$total_pages = ceil($total_reviews / $per_page);

// Получаем статистику
$stats = $review->getStats();

// Обработка AJAX запросов
if (isAjaxRequest()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    try {
        switch ($action) {
            case 'toggle_published':
                if (!Admin::hasPermission('edit_reviews')) {
                    throw new Exception('Недостаточно прав');
                }
                
                $id = intval($_POST['id'] ?? 0);
                $is_published = intval($_POST['is_published'] ?? 0);
                
                if ($review->togglePublished($id, $is_published)) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка обновления статуса');
                }
                exit;
                
            case 'add_reply':
                if (!Admin::hasPermission('edit_reviews')) {
                    throw new Exception('Недостаточно прав');
                }
                
                $id = intval($_POST['id'] ?? 0);
                $reply = trim($_POST['reply'] ?? '');
                
                if (empty($reply)) {
                    throw new Exception('Ответ не может быть пустым');
                }
                
                if ($review->addAdminReply($id, $reply, $_SESSION['admin_id'])) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка добавления ответа');
                }
                exit;
                
            case 'delete':
                if (!Admin::hasPermission('delete_reviews')) {
                    throw new Exception('Недостаточно прав');
                }
                
                $id = intval($_POST['id'] ?? 0);
                
                if ($review->deleteReview($id)) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка удаления отзыва');
                }
                exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Заголовок страницы
$page_title = 'Управление отзывами';
$current_page = 'reviews';
require_once 'includes/header.php';
?>
<style>
/* Основные стили страницы */
body {
    background-color: #f3f4f6;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container-fluid {
    padding: 2rem;
}

/* Заголовок страницы */
.services-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

/* Кнопки */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

/* Статистика */
.services-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-box h3 {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.5rem 0;
    font-weight: 400;
}

.stat-box .value {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
}

/* Фильтры */
.filters-bar {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.search-box {
    flex: 1;
    min-width: 300px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 0.5rem 1rem 0.5rem 2.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.search-box input:focus {
    outline: none;
    border-color: #3b82f6;
}

.search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

/* Селекты */
.form-control {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    background-color: white;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
}

/* Таблица услуг */
.services-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.services-table table {
    width: 100%;
    border-collapse: collapse;
}

.services-table th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    border-bottom: 1px solid #e5e7eb;
}

.services-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.service-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.service-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #e0e7ff;
    color: #4338ca;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.price-info {
    font-weight: 500;
    color: #1f2937;
}

.parameters-count, .rules-count {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.actions-cell {
    white-space: nowrap;
}

.btn-icon {
    padding: 0.375rem;
    border: none;
    background: none;
    color: #9ca3af;
    cursor: pointer;
    transition: color 0.2s;
    border-radius: 4px;
}

.btn-icon:hover {
    color: #374151;
    background-color: #f3f4f6;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

/* Модальные окна */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    max-height: calc(90vh - 150px);
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    background-color: #f9fafb;
}

/* Формы в модальном окне */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-label .required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* Чекбокс */
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #374151;
}

.checkbox-label input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    cursor: pointer;
}

/* Строка с колонками */
.row {
    display: flex;
    gap: 1rem;
    margin: 0 -0.5rem;
}

.col-md-4 {
    flex: 1;
    padding: 0 0.5rem;
}

/* Кнопки в модальном окне */
.modal-footer .btn {
    min-width: 80px;
}

/* Drag and drop для сортировки */
.sortable-ghost {
    opacity: 0.4;
    background-color: #f3f4f6;
}

.sortable-handle {
    cursor: move;
    color: #d1d5db;
}

.sortable-handle:hover {
    color: #9ca3af;
}

/* Параметры в модальном окне */
.parameter-item {
    background-color: #f9fafb;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.parameter-item.new-param {
    background-color: #eff6ff;
    border: 1px dashed #3b82f6;
}

.parameter-item .row {
    align-items: center;
}

.parameter-item .form-control {
    font-size: 0.813rem;
    padding: 0.5rem 0.75rem;
}

.parameter-item .btn-sm {
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
}

/* Кнопки удаления/сохранения в параметрах */
.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

/* Дополнительные утилиты */
.text-muted {
    color: #9ca3af;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

/* Адаптивность */
@media (max-width: 768px) {
    .services-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .filters-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        min-width: auto;
    }
    
    .services-table {
        overflow-x: auto;
    }
    
    .services-table table {
        min-width: 800px;
    }
    
    .modal-content {
        max-width: 95%;
        margin: 1rem;
    }
    
    .row {
        flex-direction: column;
    }
    
    .col-md-4 {
        width: 100%;
    }
}

/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal.active .modal-content {
    animation: fadeIn 0.3s ease-out;
}
</style>

<div class="reviews-page">
    <!-- Заголовок и статистика -->
    <div class="page-header">
        <h1>Отзывы</h1>
        <div class="header-stats">
            <div class="stat-badge">
                <span class="stat-value"><?php echo $stats['total']; ?></span>
                <span class="stat-label">Всего</span>
            </div>
            <div class="stat-badge">
                <span class="stat-value"><?php echo $stats['avg_rating']; ?></span>
                <span class="stat-label">Средний рейтинг</span>
            </div>
            <div class="stat-badge">
                <span class="stat-value"><?php echo $stats['without_reply']; ?></span>
                <span class="stat-label">Без ответа</span>
            </div>
        </div>
    </div>
    
    <!-- Фильтры -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <label>Статус</label>
                <select name="status" class="form-control">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все</option>
                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Опубликованные</option>
                    <option value="unpublished" <?php echo $status === 'unpublished' ? 'selected' : ''; ?>>Неопубликованные</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Рейтинг</label>
                <select name="rating" class="form-control">
                    <option value="">Любой</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $rating == $i ? 'selected' : ''; ?>>
                        <?php echo str_repeat('★', $i); ?> (<?php echo $i; ?>)
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Ответ админа</label>
                <select name="has_reply" class="form-control">
                    <option value="">Все</option>
                    <option value="1" <?php echo $has_reply === '1' ? 'selected' : ''; ?>>С ответом</option>
                    <option value="0" <?php echo $has_reply === '0' ? 'selected' : ''; ?>>Без ответа</option>
                </select>
            </div>
            
            <div class="filter-group">
                <input type="text" name="search" class="form-control" placeholder="Поиск..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Найти
            </button>
            
            <?php if ($status !== 'all' || $rating || $has_reply !== '' || $search): ?>
            <a href="reviews.php" class="btn btn-link">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Список отзывов -->
    <div class="reviews-list">
        <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <i class="fas fa-star-half-alt"></i>
            <p>Отзывы не найдены</p>
        </div>
        <?php else: ?>
        <?php foreach ($reviews as $review_item): ?>
        <div class="review-card <?php echo !$review_item['is_published'] ? 'unpublished' : ''; ?>" 
             data-review-id="<?php echo $review_item['id']; ?>">
            <div class="review-header">
                <div class="review-user">
                    <strong><?php echo htmlspecialchars($review_item['user_name'] ?: 'Гость'); ?></strong>
                    <?php if ($review_item['order_number']): ?>
                    <a href="order-details.php?id=<?php echo $review_item['order_id']; ?>" class="order-link">
                        Заказ #<?php echo $review_item['order_number']; ?>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="review-meta">
                    <span class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $review_item['rating'] ? 'active' : ''; ?>"></i>
                        <?php endfor; ?>
                    </span>
                    <span class="review-date"><?php echo date('d.m.Y H:i', strtotime($review_item['created_at'])); ?></span>
                </div>
            </div>
            
            <div class="review-content">
                <p><?php echo nl2br(htmlspecialchars($review_item['comment'])); ?></p>
            </div>
            
            <?php if ($review_item['admin_reply']): ?>
            <div class="review-reply">
                <div class="reply-header">
                    <i class="fas fa-reply"></i>
                    <span>Ответ администратора</span>
                    <span class="reply-date"><?php echo date('d.m.Y', strtotime($review_item['replied_at'])); ?></span>
                </div>
                <p><?php echo nl2br(htmlspecialchars($review_item['admin_reply'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="review-actions">
                <button class="btn btn-sm <?php echo $review_item['is_published'] ? 'btn-warning' : 'btn-success'; ?>" 
                        onclick="togglePublished(<?php echo $review_item['id']; ?>, <?php echo $review_item['is_published'] ? '0' : '1'; ?>)">
                    <i class="fas <?php echo $review_item['is_published'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                    <?php echo $review_item['is_published'] ? 'Скрыть' : 'Опубликовать'; ?>
                </button>
                
                <?php if (!$review_item['admin_reply']): ?>
                <button class="btn btn-sm btn-primary" onclick="showReplyModal(<?php echo $review_item['id']; ?>)">
                    <i class="fas fa-reply"></i> Ответить
                </button>
                <?php endif; ?>
                
                <?php if (Admin::hasPermission('delete_reviews')): ?>
                <button class="btn btn-sm btn-danger" onclick="deleteReview(<?php echo $review_item['id']; ?>)">
                    <i class="fas fa-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Пагинация -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
           class="<?php echo $i == $page ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Модальное окно ответа -->
<div class="modal" id="replyModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ответ на отзыв</h3>
            <button class="modal-close" onclick="closeReplyModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="replyForm" onsubmit="submitReply(event)">
            <input type="hidden" id="replyReviewId" name="review_id">
            <div class="form-group">
                <label>Ваш ответ</label>
                <textarea name="reply" class="form-control" rows="5" required 
                          placeholder="Введите ответ на отзыв..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Отправить
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeReplyModal()">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.reviews-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-stats {
    display: flex;
    gap: 20px;
}

.stat-badge {
    background: #f5f5f5;
    padding: 10px 20px;
    border-radius: 8px;
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    color: #666;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.review-card.unpublished {
    opacity: 0.7;
    background: #f9f9f9;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.review-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.order-link {
    font-size: 12px;
    color: #007bff;
    text-decoration: none;
}

.review-meta {
    display: flex;
    align-items: center;
    gap: 15px;
}

.review-rating {
    display: flex;
    gap: 2px;
}

.review-rating .fa-star {
    color: #ddd;
    font-size: 14px;
}

.review-rating .fa-star.active {
    color: #ffc107;
}

.review-date {
    font-size: 12px;
    color: #999;
}

.review-content {
    margin-bottom: 15px;
    line-height: 1.6;
}

.review-reply {
    background: #f0f4f8;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.reply-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 12px;
    color: #666;
}

.reply-header i {
    color: #007bff;
}

.review-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 20px;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #999;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
</style>

<script>
// Переключение статуса публикации
async function togglePublished(reviewId, isPublished) {
    try {
        const response = await fetch('reviews.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=toggle_published&id=${reviewId}&is_published=${isPublished}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Ошибка обновления статуса');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Показать модальное окно ответа
function showReplyModal(reviewId) {
    document.getElementById('replyReviewId').value = reviewId;
    document.getElementById('replyModal').style.display = 'flex';
}

// Закрыть модальное окно
function closeReplyModal() {
    document.getElementById('replyModal').style.display = 'none';
    document.getElementById('replyForm').reset();
}

// Отправить ответ
async function submitReply(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'add_reply');
    formData.append('id', document.getElementById('replyReviewId').value);
    
    try {
        const response = await fetch('reviews.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Ошибка добавления ответа');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Удалить отзыв
async function deleteReview(reviewId) {
    if (!confirm('Вы уверены, что хотите удалить этот отзыв?')) {
        return;
    }
    
    try {
        const response = await fetch('reviews.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=delete&id=${reviewId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.querySelector(`[data-review-id="${reviewId}"]`).remove();
        } else {
            alert(data.error || 'Ошибка удаления');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>