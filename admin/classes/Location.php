<?php
class Location {
    private $conn;
    private $table_name = "locations";

    public $id;
    public $name;
    public $code;
    public $address;
    public $phone;
    public $email;
    public $password_hash;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Authenticate location by code and password
     */
    public function authenticate($code, $password) {
        $query = "SELECT id, name, code, password_hash, is_active
                 FROM " . $this->table_name . "
                 WHERE code = :code AND is_active = 1
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $row['password_hash'])) {
                return $row;
            }
        }

        return false;
    }

    /**
     * Get all active locations
     */
    public function getAllActive() {
        $query = "SELECT id, name, code, address, phone, email, is_active
                 FROM " . $this->table_name . "
                 WHERE is_active = 1
                 ORDER BY name ASC";

        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all locations (including inactive)
     */
    public function getAll() {
        $query = "SELECT id, name, code, address, phone, email, is_active, created_at, updated_at
                 FROM " . $this->table_name . "
                 ORDER BY name ASC";

        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get location by ID
     */
    public function getById($id) {
        $query = "SELECT id, name, code, address, phone, email, is_active
                 FROM " . $this->table_name . "
                 WHERE id = :id
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new location
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 (name, code, address, phone, email, password_hash, is_active)
                 VALUES (:name, :code, :address, :phone, :email, :password_hash, :is_active)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":is_active", $this->is_active);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update location
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                 SET name = :name,
                     code = :code,
                     address = :address,
                     phone = :phone,
                     email = :email,
                     is_active = :is_active";

        if (!empty($this->password_hash)) {
            $query .= ", password_hash = :password_hash";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        if (!empty($this->password_hash)) {
            $stmt->bindParam(":password_hash", $this->password_hash);
        }

        return $stmt->execute();
    }

    /**
     * Delete location
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Toggle location status
     */
    public function toggleStatus($id) {
        $query = "UPDATE " . $this->table_name . "
                 SET is_active = NOT is_active
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Get location statistics
     */
    public function getStatistics($location_id) {
        $stats = [];

        // Total orders
        $query = "SELECT COUNT(*) as total_orders,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                        SUM(CASE WHEN status = 'in_production' THEN 1 ELSE 0 END) as in_production_orders,
                        SUM(total_price) as total_revenue
                 FROM orders
                 WHERE location_id = :location_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":location_id", $location_id);
        $stmt->execute();

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return $stats;
    }
}
