<!-- ********** PDF Leccionario Template ********** -->
<style>
    .header-title {
        font-size: 14pt;
        color: #2c3e50;
        text-align: center;
        font-weight: bold;
        border-bottom: 2px solid #3498db;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .info-table td {
        padding: 3px 0;
        border-bottom: 1px solid #ecf0f1;
        font-size: 8pt;
    }
    .info-label {
        font-weight: bold;
        color: #34495e;
        width: 18%;
    }
    .info-content {
        color: #2c3e50;
        width: 32%;
    }
    .info-content-wide {
        color: #2c3e50;
        width: 82%;
    }
    .section-title {
        font-size: 9pt;
        font-weight: bold;
        color: #ffffff;
        background-color: #3498db;
        padding: 4px 8px;
        margin-top: 8px;
        margin-bottom: 0;
    }
    .contenido-box {
        border: 1px solid #3498db;
        border-top: none;
        padding: 8px;
        min-height: 50px;
        background-color: #f8f9fa;
        font-size: 8pt;
        line-height: 1.4;
        color: #2c3e50;
    }
    .estado-completado { background-color: #27ae60; color: white; padding: 1px 6px; border-radius: 8px; }
    .estado-pendiente { background-color: #f39c12; color: white; padding: 1px 6px; border-radius: 8px; }
    .estado-atrasado { background-color: #e74c3c; color: white; padding: 1px 6px; border-radius: 8px; }
    .firma-section { margin-top: 10px; }
    .firma-title {
        font-size: 9pt;
        font-weight: bold;
        color: #34495e;
        margin-bottom: 4px;
    }
    .firma-table {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    .firma-table td {
        width: 50%;
        text-align: center;
        padding: 3px;
        margin-top: 3px;
    }
    .firma-label {
        font-weight: bold;
        color: #34495e;
        font-size: 7pt;
        margin-bottom: 2px;
    }
    .firma-nombre {
        font-size: 7pt;
        color: #666;
    }
    .footer {
        position: absolute;
        bottom: 8px;
        left: 15px;
        right: 15px;
        text-align: center;
        padding: 6px;
        background-color: #2c3e50;
        color: #ecf0f1;
        font-size: 6pt;
    }
</style>

<h1 class="header-title">DETALLE DEL LECCIONARIO</h1>

<table class="info-table">
    <tr>
        <td class="info-label">Profesor:</td>
        <td class="info-content"><?= htmlspecialchars($leccionario['profesor']) ?></td>
        <td class="info-label">Fecha:</td>
        <td class="info-content"><?= date('d/m/Y', strtotime($leccionario['fecha'])) ?></td>
    </tr>
    <tr>
        <td class="info-label">Email:</td>
        <td class="info-content-wide" colspan="3"><?= htmlspecialchars($leccionario['email']) ?></td>
    </tr>
    <tr>
        <td class="info-label">Curso:</td>
        <td class="info-content"><?= htmlspecialchars($cursoCompleto) ?></td>
        <td class="info-label">Hora:</td>
        <td class="info-content"><?= substr($leccionario['hora_inicio'] ?? '', 0, 5) ?> - <?= substr($leccionario['hora_fin'] ?? '', 0, 5) ?></td>
    </tr>
    <tr>
        <td class="info-label">Asignatura:</td>
        <td class="info-content"><?= htmlspecialchars($leccionario['asignatura']) ?></td>
        <td class="info-label">Estado:</td>
        <td class="info-content"><span class="estado-<?= $leccionario['estado'] ?>"><?= strtoupper($leccionario['estado']) ?></span></td>
    </tr>
    <tr>
        <td class="info-label">Registrado:</td>
        <td class="info-content-wide" colspan="3"><?= date('d/m/Y H:i', strtotime($leccionario['fecha_registro'])) ?></td>
    </tr>
</table>

<h3 class="section-title">CONTENIDO DESARROLLADO</h3>
<div class="contenido-box"><?= nl2br(htmlspecialchars($leccionario['contenido'] ?? '(Sin contenido registrado)')) ?></div>

<?php if (!empty($leccionario['observaciones'])): ?>
<h3 class="section-title">OBSERVACIONES</h3>
<div class="contenido-box"><?= nl2br(htmlspecialchars($leccionario['observaciones'])) ?></div>
<?php endif; ?>

<div class="firma-section">
    <h3 class="firma-title">FIRMAS</h3>
    <table class="firma-table">
        <tr>
            <td>
                <div class="firma-label">FIRMA DEL DOCENTE</div>
                <div class="firma-nombre"><?= htmlspecialchars($leccionario['profesor']) ?></div>
            </td>
            <td>
                <div class="firma-label">FIRMA DEL REVISOR</div>
                <div class="firma-nombre"><?= htmlspecialchars($nombreRevisor ?? '') ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Leccionario Digital - Desarrollado por Christian Rodriguez
</div>
