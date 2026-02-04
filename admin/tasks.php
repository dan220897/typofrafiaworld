<?php
// admin/tasks.php — Задачи и смены
require_once 'includes/auth_check.php';
require_once 'config/database.php';

checkAdminAuth();

$database = new Database();
$db = $database->getConnection();

// Получаем список точек для суперадмина
$locations = [];
if (isSuperAdmin()) {
    $stmt = $db->query("SELECT id, name FROM locations WHERE is_active = 1 ORDER BY name");
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$currentLocationId = getCurrentLocationId();

$page_title = 'Задачи';
require_once 'includes/header.php';
?>

<style>
/* ===== Основной layout ===== */
.tasks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.tasks-header h1 { margin: 0; font-size: 1.5rem; }

.location-picker {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.location-picker select {
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    background: #fff;
}

/* ===== Навигация по месяцам ===== */
.calendar-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.calendar-nav .month-label {
    font-size: 1.15rem;
    font-weight: 600;
    min-width: 180px;
    text-align: center;
    text-transform: capitalize;
}
.calendar-nav button {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 1px solid #d1d5db;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.calendar-nav button:hover { background: #f3f4f6; }

/* ===== Календарная сетка ===== */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    background: #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}
.calendar-weekday {
    background: #f9fafb;
    padding: 0.5rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
}
.calendar-day {
    background: #fff;
    min-height: 110px;
    padding: 0.5rem;
    cursor: pointer;
    transition: background 0.15s;
    position: relative;
}
.calendar-day:hover { background: #f0f9ff; }
.calendar-day.other-month {
    background: #f9fafb;
    color: #d1d5db;
}
.calendar-day.today {
    background: #eff6ff;
    box-shadow: inset 0 0 0 2px #3b82f6;
}
.calendar-day .day-number {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.35rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.calendar-day.today .day-number { color: #2563eb; }

/* ===== Задачи внутри ячейки ===== */
.day-tasks { display: flex; flex-direction: column; gap: 2px; }
.day-task {
    font-size: 0.72rem;
    padding: 2px 4px;
    border-radius: 4px;
    background: #fef3c7;
    color: #92400e;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.day-task.done {
    background: #d1fae5;
    color: #065f46;
    text-decoration: line-through;
}
.day-task-count {
    font-size: 0.7rem;
    color: #9ca3af;
    margin-top: 2px;
}

/* Индикаторы смен */
.shift-badge {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-left: 4px;
}
.shift-badge.open { background: #22c55e; }
.shift-badge.closed { background: #ef4444; }

/* ===== Панель дня (правая) ===== */
.day-panel-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.3);
    z-index: 200;
}
.day-panel-overlay.active { display: block; }

.day-panel {
    position: fixed;
    top: 0; right: -480px;
    width: 460px;
    max-width: 95vw;
    height: 100vh;
    background: #fff;
    box-shadow: -4px 0 20px rgba(0,0,0,0.15);
    z-index: 201;
    transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}
.day-panel.open { right: 0; }

.day-panel-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.day-panel-header h2 { margin: 0; font-size: 1.2rem; }
.day-panel-close {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: none;
    background: #f3f4f6;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.day-panel-close:hover { background: #e5e7eb; }

.day-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem 1.5rem;
}

/* Секция смены */
.shift-section {
    background: #f0f9ff;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid #bfdbfe;
}
.shift-section h3 {
    margin: 0 0 0.75rem;
    font-size: 0.95rem;
    color: #1e40af;
}
.shift-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}
.shift-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
}
.shift-dot.green { background: #22c55e; }
.shift-dot.red { background: #ef4444; }
.shift-dot.gray { background: #9ca3af; }

.btn-shift {
    width: 100%;
    padding: 0.65rem;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-shift.open-shift {
    background: #22c55e;
    color: #fff;
}
.btn-shift.open-shift:hover { background: #16a34a; }
.btn-shift.close-shift {
    background: #ef4444;
    color: #fff;
}
.btn-shift.close-shift:hover { background: #dc2626; }
.btn-shift:disabled {
    background: #d1d5db;
    color: #9ca3af;
    cursor: not-allowed;
}

/* Список задач в панели */
.task-list { display: flex; flex-direction: column; gap: 0.5rem; }
.task-list h3 {
    margin: 0 0 0.75rem;
    font-size: 0.95rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.task-progress {
    font-size: 0.8rem;
    font-weight: 400;
    color: #6b7280;
}

.task-item {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.65rem 0.75rem;
    border-radius: 8px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    transition: all 0.15s;
}
.task-item:hover { border-color: #3b82f6; }
.task-item.completed {
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.task-item.completed .task-title {
    text-decoration: line-through;
    color: #6b7280;
}

.task-checkbox {
    width: 20px; height: 20px;
    border-radius: 4px;
    border: 2px solid #d1d5db;
    cursor: pointer;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
    transition: all 0.15s;
    background: #fff;
}
.task-checkbox:hover { border-color: #3b82f6; }
.task-checkbox.checked {
    background: #22c55e;
    border-color: #22c55e;
    color: #fff;
}

.task-title {
    flex: 1;
    font-size: 0.9rem;
    line-height: 1.4;
}
.task-delete {
    width: 24px; height: 24px;
    border: none;
    background: none;
    cursor: pointer;
    color: #d1d5db;
    font-size: 0.85rem;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.task-delete:hover { color: #ef4444; background: #fef2f2; }

.no-tasks {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
    font-size: 0.9rem;
}

/* Форма добавления задачи */
.add-task-form {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}
.add-task-form input {
    flex: 1;
    padding: 0.55rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
}
.add-task-form input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
}
.add-task-form button {
    padding: 0.55rem 1rem;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.9rem;
    white-space: nowrap;
}
.add-task-form button:hover { background: #2563eb; }

/* Мобильная адаптация */
@media (max-width: 768px) {
    .calendar-grid { gap: 1px; }
    .calendar-day { min-height: 60px; padding: 0.3rem; }
    .day-tasks { display: none; }
    .day-panel { width: 100%; max-width: 100%; }
}
</style>

<div class="tasks-header">
    <h1><i class="fas fa-tasks"></i> Задачи</h1>
    <?php if (isSuperAdmin() && !empty($locations)): ?>
    <div class="location-picker">
        <label>Точка:</label>
        <select id="locationSelect" onchange="onLocationChange()">
            <option value="">Выберите точку</option>
            <?php foreach ($locations as $loc): ?>
            <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>

<!-- Навигация по месяцам -->
<div class="calendar-nav">
    <button onclick="prevMonth()"><i class="fas fa-chevron-left"></i></button>
    <div class="month-label" id="monthLabel"></div>
    <button onclick="nextMonth()"><i class="fas fa-chevron-right"></i></button>
</div>

<!-- Календарная сетка -->
<div class="calendar-grid" id="calendarGrid"></div>

<!-- Панель дня -->
<div class="day-panel-overlay" id="dayPanelOverlay" onclick="closeDayPanel()"></div>
<div class="day-panel" id="dayPanel">
    <div class="day-panel-header">
        <h2 id="dayPanelTitle">--</h2>
        <button class="day-panel-close" onclick="closeDayPanel()"><i class="fas fa-times"></i></button>
    </div>
    <div class="day-panel-body" id="dayPanelBody">
        <!-- Заполняется JS -->
    </div>
</div>

<script>
const IS_SUPER = <?php echo isSuperAdmin() ? 'true' : 'false'; ?>;
const IS_LOCATION = <?php echo isLocationAdmin() ? 'true' : 'false'; ?>;
const MY_LOCATION_ID = <?php echo $currentLocationId ? intval($currentLocationId) : 'null'; ?>;

const MONTH_NAMES = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
const WEEKDAYS = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];

let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth(); // 0-based
let selectedLocationId = MY_LOCATION_ID;
let tasksData = {};
let shiftsData = {};

function getLocationId() {
    if (IS_LOCATION) return MY_LOCATION_ID;
    return selectedLocationId;
}

function onLocationChange() {
    selectedLocationId = parseInt(document.getElementById('locationSelect').value) || null;
    if (selectedLocationId) loadData();
    else renderCalendar();
}

function prevMonth() {
    currentMonth--;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    loadData();
}
function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    loadData();
}

// ===== Загрузка данных =====
async function loadData() {
    var locId = getLocationId();
    if (!locId) { renderCalendar(); return; }
    var m = currentMonth + 1;
    try {
        var [tasksRes, shiftsRes] = await Promise.all([
            fetch('api/tasks.php?action=get_tasks&location_id=' + locId + '&month=' + m + '&year=' + currentYear).then(r => r.json()),
            fetch('api/tasks.php?action=get_shifts_month&location_id=' + locId + '&month=' + m + '&year=' + currentYear).then(r => r.json())
        ]);
        tasksData = tasksRes.success ? tasksRes.tasks : {};
        shiftsData = shiftsRes.success ? shiftsRes.shifts : {};
    } catch(e) {
        tasksData = {};
        shiftsData = {};
    }
    renderCalendar();
}

// ===== Рендер календаря =====
function renderCalendar() {
    var grid = document.getElementById('calendarGrid');
    document.getElementById('monthLabel').textContent = MONTH_NAMES[currentMonth] + ' ' + currentYear;

    var html = '';
    WEEKDAYS.forEach(function(d) { html += '<div class="calendar-weekday">' + d + '</div>'; });

    var firstDay = new Date(currentYear, currentMonth, 1);
    var lastDay = new Date(currentYear, currentMonth + 1, 0);
    var startDow = (firstDay.getDay() + 6) % 7; // 0=Пн

    var today = new Date();
    var todayStr = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');

    // Пустые ячейки до первого дня
    var prevMonthLast = new Date(currentYear, currentMonth, 0).getDate();
    for (var i = startDow - 1; i >= 0; i--) {
        html += '<div class="calendar-day other-month"><div class="day-number">' + (prevMonthLast - i) + '</div></div>';
    }

    // Дни месяца
    for (var d = 1; d <= lastDay.getDate(); d++) {
        var dateStr = currentYear + '-' + String(currentMonth+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        var isToday = dateStr === todayStr;
        var cls = 'calendar-day' + (isToday ? ' today' : '');
        var dayTasks = tasksData[dateStr] || [];
        var shift = shiftsData[dateStr] || null;

        html += '<div class="' + cls + '" onclick="openDayPanel(\'' + dateStr + '\')">';
        html += '<div class="day-number">' + d;
        if (shift) {
            html += '<span class="shift-badge ' + (shift.closed_at ? 'closed' : 'open') + '" title="' + (shift.closed_at ? 'Смена закрыта' : 'Смена открыта') + '"></span>';
        }
        html += '</div>';

        // Задачи (макс 3)
        html += '<div class="day-tasks">';
        var shown = Math.min(dayTasks.length, 3);
        for (var t = 0; t < shown; t++) {
            var task = dayTasks[t];
            var done = !!task.completed_at;
            html += '<div class="day-task' + (done ? ' done' : '') + '">' + escapeHtml(task.title) + '</div>';
        }
        if (dayTasks.length > 3) {
            html += '<div class="day-task-count">+ ещё ' + (dayTasks.length - 3) + '</div>';
        }
        html += '</div></div>';
    }

    // Пустые ячейки после последнего дня
    var endDow = (lastDay.getDay() + 6) % 7;
    for (var i = 1; i < 7 - endDow; i++) {
        html += '<div class="calendar-day other-month"><div class="day-number">' + i + '</div></div>';
    }

    grid.innerHTML = html;
}

// ===== Панель дня =====
var currentPanelDate = null;

function openDayPanel(dateStr) {
    currentPanelDate = dateStr;
    var locId = getLocationId();
    if (!locId) {
        showNotification('Сначала выберите точку', 'error');
        return;
    }

    var parts = dateStr.split('-');
    var dayNum = parseInt(parts[2]);
    var monthNum = parseInt(parts[1]) - 1;
    document.getElementById('dayPanelTitle').textContent = dayNum + ' ' + MONTH_NAMES[monthNum] + ' ' + parts[0];
    document.getElementById('dayPanelOverlay').classList.add('active');
    document.getElementById('dayPanel').classList.add('open');

    renderDayPanel(dateStr);
}

function closeDayPanel() {
    document.getElementById('dayPanelOverlay').classList.remove('active');
    document.getElementById('dayPanel').classList.remove('open');
    currentPanelDate = null;
}

function renderDayPanel(dateStr) {
    var body = document.getElementById('dayPanelBody');
    var dayTasks = tasksData[dateStr] || [];
    var shift = shiftsData[dateStr] || null;
    var today = new Date();
    var todayStr = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');
    var isToday = dateStr === todayStr;

    var html = '';

    // ===== Секция смены (только для текущего дня и админа точки) =====
    if (IS_LOCATION && isToday) {
        html += '<div class="shift-section">';
        html += '<h3><i class="fas fa-clock"></i> Смена</h3>';

        if (!shift) {
            html += '<div class="shift-status"><span class="shift-dot gray"></span> Смена не открыта</div>';
            html += '<button class="btn-shift open-shift" onclick="openShift()"><i class="fas fa-play"></i> Открыть смену</button>';
        } else if (!shift.closed_at) {
            html += '<div class="shift-status"><span class="shift-dot green"></span> Смена открыта с ' + formatTime(shift.opened_at) + '</div>';
            html += '<button class="btn-shift close-shift" onclick="closeShift()"><i class="fas fa-stop"></i> Закрыть смену</button>';
        } else {
            html += '<div class="shift-status"><span class="shift-dot red"></span> Смена закрыта</div>';
            html += '<div style="font-size:0.85rem;color:#6b7280;">Открыта: ' + formatTime(shift.opened_at) + ' — Закрыта: ' + formatTime(shift.closed_at) + '</div>';
        }
        html += '</div>';
    } else if (shift) {
        // Суперадмин или другой день — просто показываем инфо
        html += '<div class="shift-section">';
        html += '<h3><i class="fas fa-clock"></i> Смена</h3>';
        if (!shift.closed_at) {
            html += '<div class="shift-status"><span class="shift-dot green"></span> Открыта с ' + formatTime(shift.opened_at) + '</div>';
        } else {
            html += '<div class="shift-status"><span class="shift-dot red"></span> ' + formatTime(shift.opened_at) + ' — ' + formatTime(shift.closed_at) + '</div>';
        }
        html += '</div>';
    }

    // ===== Задачи =====
    var doneCount = dayTasks.filter(function(t) { return !!t.completed_at; }).length;

    html += '<div class="task-list">';
    html += '<h3>Задачи';
    if (dayTasks.length > 0) {
        html += ' <span class="task-progress">' + doneCount + ' / ' + dayTasks.length + '</span>';
    }
    html += '</h3>';

    if (dayTasks.length === 0) {
        html += '<div class="no-tasks"><i class="fas fa-check-circle" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>Нет задач на этот день</div>';
    } else {
        dayTasks.forEach(function(task) {
            var isDone = !!task.completed_at;
            html += '<div class="task-item' + (isDone ? ' completed' : '') + '" data-task-id="' + task.id + '">';
            html += '<div class="task-checkbox' + (isDone ? ' checked' : '') + '" onclick="toggleTask(' + task.id + ',' + (isDone ? 'false' : 'true') + ')">';
            if (isDone) html += '<i class="fas fa-check" style="font-size:0.7rem;"></i>';
            html += '</div>';
            html += '<div class="task-title">' + escapeHtml(task.title) + '</div>';
            if (IS_SUPER) {
                html += '<button class="task-delete" onclick="deleteTask(' + task.id + ')" title="Удалить"><i class="fas fa-trash"></i></button>';
            }
            html += '</div>';
        });
    }

    // Форма добавления (только суперадмин)
    if (IS_SUPER) {
        html += '<div class="add-task-form">';
        html += '<input type="text" id="newTaskInput" placeholder="Новая задача..." onkeydown="if(event.key===\'Enter\')addTask()">';
        html += '<button onclick="addTask()"><i class="fas fa-plus"></i> Добавить</button>';
        html += '</div>';
    }
    html += '</div>';

    body.innerHTML = html;
}

// ===== API вызовы =====

async function toggleTask(taskId, completed) {
    try {
        var res = await fetch('api/tasks.php?action=toggle_task', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({task_id: taskId, completed: completed})
        }).then(r => r.json());
        if (!res.success) { showNotification(res.error, 'error'); return; }

        // Обновляем локальные данные
        for (var date in tasksData) {
            tasksData[date].forEach(function(t) {
                if (t.id == taskId) {
                    t.completed_at = completed ? new Date().toISOString() : null;
                }
            });
        }
        renderCalendar();
        if (currentPanelDate) renderDayPanel(currentPanelDate);
    } catch(e) {
        showNotification('Ошибка сети', 'error');
    }
}

async function addTask() {
    var input = document.getElementById('newTaskInput');
    var title = input.value.trim();
    if (!title) return;

    try {
        var res = await fetch('api/tasks.php?action=add_task', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                location_id: getLocationId(),
                task_date: currentPanelDate,
                title: title
            })
        }).then(r => r.json());
        if (!res.success) { showNotification(res.error, 'error'); return; }

        // Добавляем в локальные данные
        if (!tasksData[currentPanelDate]) tasksData[currentPanelDate] = [];
        tasksData[currentPanelDate].push({
            id: res.task_id,
            title: title,
            task_date: currentPanelDate,
            completed_at: null
        });

        input.value = '';
        renderCalendar();
        renderDayPanel(currentPanelDate);
        showNotification('Задача добавлена', 'success');
    } catch(e) {
        showNotification('Ошибка сети', 'error');
    }
}

async function deleteTask(taskId) {
    if (!confirm('Удалить задачу?')) return;
    try {
        var res = await fetch('api/tasks.php?action=delete_task', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({task_id: taskId})
        }).then(r => r.json());
        if (!res.success) { showNotification(res.error, 'error'); return; }

        // Убираем из локальных данных
        for (var date in tasksData) {
            tasksData[date] = tasksData[date].filter(function(t) { return t.id != taskId; });
        }
        renderCalendar();
        renderDayPanel(currentPanelDate);
        showNotification('Задача удалена', 'success');
    } catch(e) {
        showNotification('Ошибка сети', 'error');
    }
}

async function openShift() {
    try {
        var res = await fetch('api/tasks.php?action=open_shift', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({})
        }).then(r => r.json());
        if (!res.success) { showNotification(res.error, 'error'); return; }

        // Обновляем локальные данные
        var today = currentPanelDate;
        shiftsData[today] = {
            opened_at: new Date().toISOString(),
            closed_at: null
        };
        renderCalendar();
        renderDayPanel(today);
        showNotification('Смена открыта', 'success');
    } catch(e) {
        showNotification('Ошибка сети', 'error');
    }
}

async function closeShift() {
    if (!confirm('Закрыть смену?')) return;
    try {
        var res = await fetch('api/tasks.php?action=close_shift', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({})
        }).then(r => r.json());
        if (!res.success) { showNotification(res.error, 'error'); return; }

        var today = currentPanelDate;
        if (shiftsData[today]) {
            shiftsData[today].closed_at = new Date().toISOString();
        }
        renderCalendar();
        renderDayPanel(today);
        showNotification('Смена закрыта', 'success');
    } catch(e) {
        showNotification('Ошибка сети', 'error');
    }
}

// ===== Утилиты =====
function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatTime(dateStr) {
    if (!dateStr) return '--:--';
    var d = new Date(dateStr);
    if (isNaN(d.getTime())) {
        // Может быть MySQL-формат "2026-02-04 10:30:00"
        var parts = dateStr.split(' ');
        if (parts.length === 2) return parts[1].substring(0, 5);
        return dateStr;
    }
    return String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
}

// ===== Инициализация =====
renderCalendar();
if (getLocationId()) loadData();
</script>

<?php require_once 'includes/footer.php'; ?>
