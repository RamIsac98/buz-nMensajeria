<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudDesechosModel extends Model
{
    protected $table      = 'solicitudes_desechos';
    protected $primaryKey = 'id';

    public function generarCodigoUnico(): string
    {
        $prefix = "SOL-" . date('Y');
        $sql = "SELECT COUNT(id) as total FROM solicitudes_desechos WHERE codigo_solicitud LIKE ?";
        $row = $this->db->query($sql, [$prefix . '%'])->getRowArray();
        $secuencia = str_pad(($row['total'] + 1), 4, '0', STR_PAD_LEFT);
        return $prefix . "-" . $secuencia;
    }

    public function insertarSolicitud(array $data): bool
    {
        // AÑADIDO: 'ruta_pdf' a la consulta SQL
        $sql = "INSERT INTO solicitudes_desechos 
            (codigo_solicitud, usuario_id, ext_telefono, tipos_desecho, variantes_desecho, esterilizado, motivo, estado, peso_kg, peso_l, tipo_empaque, empaque_otro_descripcion, ruta_pdf) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        return $this->db->query($sql, [
            $data['codigo_solicitud'],
            $data['usuario_id'],
            $data['ext_telefono'],
            $data['tipos_desecho'],
            $data['variantes_desecho'],
            $data['esterilizado'],
            $data['motivo'],
            $data['estado'],
            $data['peso_kg'] ?? 0.00,
            $data['peso_l'] ?? 0.00,
            $data['tipo_empaque'],
            $data['empaque_otro_descripcion'] ?? null,
            $data['ruta_pdf'] ?? null // ESTO ES VITAL
        ]);
    }

    public function getSolicitudes()
    {
        $sql = "SELECT s.*, s.ruta_pdf, u.username, u.cedula,
                    d.nombre AS nombre_departamento, 
                    l.nombre AS nombre_laboratorio
                FROM solicitudes_desechos s
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                LEFT JOIN laboratorios l ON s.usuario_id = l.id
                LEFT JOIN departamentos d ON l.departamento_id = d.id
                ORDER BY s.id DESC";
        return $this->db->query($sql)->getResultArray();
    }
}