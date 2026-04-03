<?php

require_once dirname(__DIR__) . '/Libraries/tcpdf/tcpdf.php';

class PdfGenerator
{
    private $tcpdf;

    public function __construct()
    {
        $this->tcpdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->tcpdf->SetCreator('Leccionario Digital');
        $this->tcpdf->SetAuthor('Christian Rodriguez');
        $this->tcpdf->SetTitle('Leccionario');
        $this->tcpdf->SetMargins(20, 20, 20);
        $this->tcpdf->SetAutoPageBreak(true, 25);
    }

    public function generarLeccionario(array $leccionario, ?string $firma = null): string
    {
        $this->tcpdf->AddPage();
        
        $estadoColor = $this->getEstadoColor($leccionario['estado']);
        
        $html = '
        <style>
            body { font-family: helvetica; }
            .header-title { 
                font-size: 20pt; 
                color: #2c3e50; 
                text-align: center; 
                font-weight: bold;
                border-bottom: 3px solid #3498db;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .info-table td { padding: 8px 5px; border-bottom: 1px solid #ecf0f1; }
            .info-label { font-weight: bold; color: #34495e; width: 25%; font-size: 10pt; }
            .info-content { color: #2c3e50; font-size: 10pt; }
            .section-title { 
                font-size: 11pt; 
                font-weight: bold; 
                color: #ffffff; 
                background-color: #3498db;
                padding: 8px 12px;
                margin-top: 15px;
                margin-bottom: 0;
            }
            .contenido-box { 
                border: 2px solid #3498db; 
                border-top: none;
                padding: 20px; 
                min-height: 120px; 
                background-color: #f8f9fa;
                font-size: 10pt;
                line-height: 1.6;
                color: #2c3e50;
            }
            .estado-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 15px;
                font-weight: bold;
                font-size: 9pt;
            }
            .estado-completado { background-color: #27ae60; color: white; }
            .estado-pendiente { background-color: #f39c12; color: white; }
            .estado-atrasado { background-color: #e74c3c; color: white; }
            .firma-section {
                margin-top: 30px;
                page-break-inside: avoid;
            }
            .firma-title {
                font-size: 11pt;
                font-weight: bold;
                color: #34495e;
                margin-bottom: 10px;
            }
            .firma-container {
                display: table;
                width: 100%;
                border-collapse: collapse;
            }
            .firma-box {
                display: table-cell;
                width: 48%;
                border: 2px solid #bdc3c7;
                padding: 15px;
                text-align: center;
                vertical-align: bottom;
                min-height: 120px;
                background-color: #fefefe;
            }
            .firma-box:first-child { margin-right: 4%; }
            .firma-label {
                font-weight: bold;
                color: #7f8c8d;
                font-size: 9pt;
                margin-bottom: 10px;
            }
            .firma-image {
                max-height: 70px;
                max-width: 200px;
            }
            .firma-placeholder {
                color: #bdc3c7;
                font-style: italic;
                font-size: 9pt;
            }
            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                padding: 15px 20px;
                background-color: #2c3e50;
                color: #ecf0f1;
                font-size: 8pt;
                margin-left: -20px;
                margin-right: -20px;
                margin-bottom: -20px;
            }
            .footer-main { font-weight: bold; margin-bottom: 3px; }
            .footer-author { color: #bdc3c7; font-size: 7pt; }
            .watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 80pt;
                color: rgba(0,0,0,0.03);
                z-index: -1;
                pointer-events: none;
            }
        </style>
        
        <div class="watermark">LECCIONARIO</div>
        
        <h1 class="header-title">DETALLE DEL LECCIONARIO</h1>
        
        <table class="info-table">
            <tr>
                <td class="info-label">Profesor:</td>
                <td class="info-content">' . htmlspecialchars($leccionario['profesor']) . '</td>
                <td class="info-label">Email:</td>
                <td class="info-content">' . htmlspecialchars($leccionario['email']) . '</td>
            </tr>
            <tr>
                <td class="info-label">Curso:</td>
                <td class="info-content">' . htmlspecialchars($leccionario['curso']) . '</td>
                <td class="info-label">Asignatura:</td>
                <td class="info-content">' . htmlspecialchars($leccionario['asignatura']) . '</td>
            </tr>
            <tr>
                <td class="info-label">Fecha:</td>
                <td class="info-content">' . date('d/m/Y', strtotime($leccionario['fecha'])) . '</td>
                <td class="info-label">Hora:</td>
                <td class="info-content">' . substr($leccionario['hora_inicio'], 0, 5) . ' - ' . substr($leccionario['hora_fin'], 0, 5) . '</td>
            </tr>
            <tr>
                <td class="info-label">Estado:</td>
                <td class="info-content"><span class="estado-badge ' . $estadoColor['class'] . '">' . strtoupper($leccionario['estado']) . '</span></td>
                <td class="info-label">Registrado:</td>
                <td class="info-content">' . date('d/m/Y H:i', strtotime($leccionario['fecha_registro'])) . '</td>
            </tr>
        </table>
        
        <h3 class="section-title">CONTENIDO DESARROLLADO</h3>
        <div class="contenido-box">' . nl2br(htmlspecialchars($leccionario['contenido'] ?? '(Sin contenido registrado)')) . '</div>';

        if (!empty($leccionario['observaciones'])) {
            $html .= '
        <h3 class="section-title">OBSERVACIONES</h3>
        <div class="contenido-box">' . nl2br(htmlspecialchars($leccionario['observaciones'])) . '</div>';
        }
        
        $html .= '
        <div class="firma-section">
            <h3 class="firma-title">FIRMAS</h3>
            <div class="firma-container">
                <div class="firma-box">
                    <div class="firma-label">FIRMA DEL DOCENTE</div>';
        
        if ($firma && $leccionario['firmado']) {
            $imgData = 'data://image/png;base64,' . base64_encode($firma);
            $html .= '<img src="' . $imgData . '" class="firma-image">';
        } else {
            $html .= '<div class="firma-placeholder">Sin firma</div>';
        }
        
        $html .= '
                </div>
                <div class="firma-box">
                    <div class="firma-label">FIRMA DEL REVISOR</div>
                    <div class="firma-placeholder">________________</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-main">Creado por Leccionario Digital - Todos los derechos reservados</div>
            <div class="footer-author">Desarrollado por: Christian Rodriguez</div>
        </div>';

        $this->tcpdf->writeHTML($html, true, false, true, false, '');
        
        return $this->tcpdf->Output('leccionario_' . $leccionario['id'] . '.pdf', 'S');
    }

    private function getEstadoColor(string $estado): array
    {
        switch ($estado) {
            case 'completado':
                return ['class' => 'estado-completado', 'bg' => '#27ae60'];
            case 'atrasado':
                return ['class' => 'estado-atrasado', 'bg' => '#e74c3c'];
            default:
                return ['class' => 'estado-pendiente', 'bg' => '#f39c12'];
        }
    }

    public function enviarRespuesta(string $pdf, string $nombre): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}
