-- ============================================
-- МИГРАЦИЯ: Система задач и смен для точек
-- Дата: 2026-02-04
-- Описание: Таблицы tasks, task_completions, shifts
-- ============================================

-- Задачи (создаются суперадмином для конкретной точки на конкретный день)
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL COMMENT 'Точка, для которой задача',
  `task_date` date NOT NULL COMMENT 'Дата задачи',
  `title` varchar(500) NOT NULL COMMENT 'Текст задачи',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(50) NOT NULL COMMENT 'ID админа, создавшего задачу',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_location_date` (`location_id`, `task_date`),
  KEY `idx_task_date` (`task_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Отметки о выполнении задач (ставятся админом точки)
CREATE TABLE IF NOT EXISTS `task_completions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `completed_by` varchar(50) NOT NULL COMMENT 'ID админа точки',
  `completed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_task` (`task_id`),
  CONSTRAINT `fk_tc_task` FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Смены (открытие/закрытие)
CREATE TABLE IF NOT EXISTS `shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `opened_by` varchar(50) DEFAULT NULL,
  `closed_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_location_date` (`location_id`, `shift_date`),
  KEY `idx_shift_date` (`shift_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
