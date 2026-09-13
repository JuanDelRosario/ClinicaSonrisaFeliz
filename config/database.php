<?php
/**
 * Configuración de la Base de Datos
 * Clínica Dental "Mi Sonrisa Feliz"
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'clinica_sonrisa_feliz_v2');
define('DB_PORT', 3306);

// Crear conexión
$conn = null;

function connectDB() {
    try {
        $conn = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASSWORD,
            DB_NAME,
            DB_PORT
        );

        // Verificar conexión
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }

        // Establecer charset UTF-8
        $conn->set_charset("utf8mb4");

        return $conn;
    } catch (Exception $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Error de conexión a la base de datos. Por favor, intente más tarde.");
    }
}

// Obtener conexión global
$GLOBALS['db_connection'] = connectDB();

/**
 * Función para ejecutar queries
 */
function executeQuery($sql, $params = []) {
    $conn = $GLOBALS['db_connection'];
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Crear string de tipos
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt;
    } else {
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }

        return $result;
    }
}

/**
 * Función para obtener un registro
 */
function getRecord($sql, $params = []) {
    try {
        $result = executeQuery($sql, $params);
        
        if ($result instanceof mysqli_stmt) {
            $result = $result->get_result();
        }
        
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Database Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Función para obtener múltiples registros
 */
function getRecords($sql, $params = []) {
    try {
        $result = executeQuery($sql, $params);
        
        if ($result instanceof mysqli_stmt) {
            $result = $result->get_result();
        }
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return $records;
    } catch (Exception $e) {
        error_log("Database Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Función para insertar
 */
function insertRecord($table, $data) {
    try {
        $conn = $GLOBALS['db_connection'];
        
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $types = '';
        $params = [];
        foreach ($data as $value) {
            $params[] = $value;
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $conn->insert_id;
    } catch (Exception $e) {
        error_log("Insert Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Función para actualizar
 */
function updateRecord($table, $data, $where_column, $where_value) {
    try {
        $conn = $GLOBALS['db_connection'];
        
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        
        $params[] = $where_value;
        
        $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE $where_column = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $types = '';
        foreach ($params as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    } catch (Exception $e) {
        error_log("Update Error: " . $e->getMessage());
        return false;
    }
}

?>
