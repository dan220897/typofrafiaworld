<?php
// admin/classes/Review.php

class Review {
    private $conn;
    private $table_name = "reviews";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Получить список отзывов
    public function getReviews($filters = [], $limit = 20, $offset = 0) {
        $query = "SELECT r.*, 
                        u.name as user_name, 
                        u.phone as user_phone,
                        o.order_number,
                        a.full_name as admin_name
                 FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.id
                 LEFT JOIN orders o ON r.order_id = o.id
                 LEFT JOIN admins a ON r.replied_by = a.id
                 WHERE 1=1";
        
        // Применяем фильтры
        if (isset($filters['is_published'])) {
            $query .= " AND r.is_published = :is_published";
        }
        
        if (!empty($filters['rating'])) {
            $query .= " AND r.rating = :rating";
        }
        
        if (!empty($filters['has_reply'])) {
            if ($filters['has_reply'] == '1') {
                $query .= " AND r.admin_reply IS NOT NULL";
            } else {
                $query .= " AND r.admin_reply IS NULL";
            }
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (r.comment LIKE :search OR u.name LIKE :search)";
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND r.created_at >= :date_from";
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND r.created_at <= :date_to";
        }
        
        $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($filters['is_published'])) {
            $stmt->bindParam(":is_published", $filters['is_published']);
        }
        
        if (!empty($filters['rating'])) {
            $stmt->bindParam(":rating", $filters['rating']);
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        if (!empty($filters['date_from'])) {
            $stmt->bindParam(":date_from", $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $stmt->bindParam(":date_to", $filters['date_to']);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Получить количество отзывов
    public function getReviewsCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.id
                 WHERE 1=1";
        
        if (isset($filters['is_published'])) {
            $query .= " AND r.is_published = :is_published";
        }
        
        if (!empty($filters['rating'])) {
            $query .= " AND r.rating = :rating";
        }
        
        if (!empty($filters['has_reply'])) {
            if ($filters['has_reply'] == '1') {
                $query .= " AND r.admin_reply IS NOT NULL";
            } else {
                $query .= " AND r.admin_reply IS NULL";
            }
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (r.comment LIKE :search OR u.name LIKE :search)";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($filters['is_published'])) {
            $stmt->bindParam(":is_published", $filters['is_published']);
        }
        
        if (!empty($filters['rating'])) {
            $stmt->bindParam(":rating", $filters['rating']);
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    // Получить отзыв по ID
    public function getReviewById($id) {
        $query = "SELECT r.*, 
                        u.name as user_name, 
                        u.phone as user_phone,
                        o.order_number,
                        a.full_name as admin_name
                 FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.id
                 LEFT JOIN orders o ON r.order_id = o.id
                 LEFT JOIN admins a ON r.replied_by = a.id
                 WHERE r.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Обновить статус публикации
    public function togglePublished($id, $is_published) {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_published = :is_published 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":is_published", $is_published);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Добавить ответ администратора
    public function addAdminReply($id, $reply, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET admin_reply = :reply, 
                     replied_by = :admin_id,
                     replied_at = NOW() 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reply", $reply);
        $stmt->bindParam(":admin_id", $admin_id);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Удалить отзыв
    public function deleteReview($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Получить статистику по отзывам
    public function getStats() {
        $stats = [];
        
        // Общее количество
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->query($query);
        $stats['total'] = $stmt->fetch()['total'];
        
        // По рейтингам
        $query = "SELECT rating, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 GROUP BY rating";
        $stmt = $this->conn->query($query);
        $stats['by_rating'] = $stmt->fetchAll();
        
        // Средний рейтинг
        $query = "SELECT AVG(rating) as avg_rating FROM " . $this->table_name;
        $stmt = $this->conn->query($query);
        $stats['avg_rating'] = round($stmt->fetch()['avg_rating'], 1);
        
        // Без ответа
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE admin_reply IS NULL";
        $stmt = $this->conn->query($query);
        $stats['without_reply'] = $stmt->fetch()['count'];
        
        // Неопубликованные
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE is_published = 0";
        $stmt = $this->conn->query($query);
        $stats['unpublished'] = $stmt->fetch()['count'];
        
        return $stats;
    }
}