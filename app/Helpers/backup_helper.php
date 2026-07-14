<?php
// app/Helpers/backup_helper.php - VERSIÓN PHP PURO (SIN MYSQLDUMP)

if (!function_exists('create_database_backup')) {
    function create_database_backup($tables = '*', $output = null) {
        try {
            $db = \Config\Database::connect();
            
            // Si $tables es '*', obtener todas las tablas
            if ($tables == '*') {
                $tables = $db->listTables();
            } elseif (is_string($tables)) {
                $tables = [$tables];
            }
            
            // Crear carpeta de backups
            $backupPath = WRITEPATH . 'backups/';
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0777, true);
            }
            
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupPath . $filename;
            
            // Abrir archivo para escritura
            $file = fopen($filepath, 'w');
            if (!$file) {
                throw new Exception("No se pudo crear el archivo de backup");
            }
            
            // Escribir cabecera
            fwrite($file, "-- Backup generado: " . date('Y-m-d H:i:s') . "\n");
            fwrite($file, "-- Base de datos: " . $db->getDatabase() . "\n\n");
            
            // Deshabilitar FK checks
            fwrite($file, "SET FOREIGN_KEY_CHECKS=0;\n\n");
            
            foreach ($tables as $table) {
                // Obtener CREATE TABLE
                $createTable = $db->query("SHOW CREATE TABLE `$table`")->getRow();
                if ($createTable) {
                    $createSQL = $createTable->{'Create Table'};
                    fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");
                    fwrite($file, "$createSQL;\n\n");
                }
                
                // Obtener datos
                $query = $db->query("SELECT * FROM `$table`");
                $results = $query->getResultArray();
                
                if (!empty($results)) {
                    $fields = array_keys($results[0]);
                    $fieldsStr = implode('`, `', $fields);
                    
                    // Agrupar inserciones en lotes de 100 para mejor rendimiento
                    $batchSize = 100;
                    for ($i = 0; $i < count($results); $i += $batchSize) {
                        $batch = array_slice($results, $i, $batchSize);
                        foreach ($batch as $row) {
                            $values = array_map(function($value) use ($db) {
                                if ($value === null) {
                                    return 'NULL';
                                }
                                return "'" . $db->escapeString($value) . "'";
                            }, $row);
                            $valuesStr = implode(', ', $values);
                            fwrite($file, "INSERT INTO `$table` (`$fieldsStr`) VALUES ($valuesStr);\n");
                        }
                        fwrite($file, "\n");
                    }
                }
            }
            
            // Rehabilitar FK checks
            fwrite($file, "SET FOREIGN_KEY_CHECKS=1;\n");
            
            fclose($file);
            
            // Comprimir si es necesario (más de 1 MB)
            if (function_exists('gzencode') && filesize($filepath) > 1024 * 1024) {
                $content = file_get_contents($filepath);
                file_put_contents($filepath . '.gz', gzencode($content, 9));
                unlink($filepath);
                $filename .= '.gz';
            }
            
            log_message('info', 'Backup creado exitosamente (PHP puro): ' . $filename);
            return $filename;
            
        } catch (\Exception $e) {
            log_message('error', 'Backup falló: ' . $e->getMessage());
            throw $e;
        }
    }
}

// ===== FUNCIONES AUXILIARES =====

if (!function_exists('list_backups')) {
    function list_backups() {
        $backupPath = WRITEPATH . 'backups/';
        $backups = [];
        
        if (is_dir($backupPath)) {
            $files = glob($backupPath . '*.sql');
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'path' => $file
                ];
            }
        }
        
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
}

if (!function_exists('delete_backup')) {
    function delete_backup($filename) {
        $backupPath = WRITEPATH . 'backups/';
        $filepath = $backupPath . $filename;
        
        if (file_exists($filepath) && unlink($filepath)) {
            log_message('info', 'Backup eliminado: ' . $filename);
            return true;
        }
        
        return false;
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes($bytes) {
        if ($bytes === 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}

if (!function_exists('clean_old_backups')) {
    /**
     * Elimina los backups más antiguos si se supera el límite
     * @param int $limit Número máximo de backups a mantener
     */
    function clean_old_backups($limit = 30) {
        $backupPath = WRITEPATH . 'backups/';
        if (!is_dir($backupPath)) {
            return;
        }

        // Obtener todos los archivos .sql y .sql.gz
        $files = glob($backupPath . 'backup_*.sql*');
        
        // Ordenar por fecha de modificación (más reciente primero)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Si hay más del límite, eliminar los sobrantes
        if (count($files) > $limit) {
            $toDelete = array_slice($files, $limit);
            foreach ($toDelete as $file) {
                if (unlink($file)) {
                    log_message('info', 'Backup antiguo eliminado automáticamente: ' . basename($file));
                }
            }
        }
    }
}