<?php
/** Location: leccionario-digital/app/Core/PdfGenerator.php */

require_once dirname(__DIR__) . '/Libraries/tcpdf/tcpdf.php';

class PdfGenerator
{
    private $tcpdf;
    private ?string $firmaDocente = null;
    private ?string $firmaRevisor = null;

    public function __construct()
    {
        $this->tcpdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->tcpdf->SetCreator('Leccionario Digital');
        $this->tcpdf->SetAuthor('Christian Rodriguez');
        $this->tcpdf->SetTitle('Leccionario');
        $this->tcpdf->SetMargins(15, 15, 15);
        $this->tcpdf->SetAutoPageBreak(false);
    }

    public function generarLeccionario(array $leccionario, ?string $firma = null, ?string $firmaRevisor = null, ?string $nombreRevisor = null): string
    {
        $this->firmaDocente = $firma;
        $this->firmaRevisor = $firmaRevisor;
        
        $this->tcpdf->AddPage();
        
        $cursoCompleto = trim(($leccionario['curso'] ?? '') . ' ' . ($leccionario['seccion'] ?? ''));
        $tieneFirmaDocente = !empty($this->firmaDocente);
        $tieneFirmaRevisor = !empty($this->firmaRevisor);

        ob_start();
        include dirname(__DIR__) . '/Views/coordinador/pdf-leccionario.php';
        $html = ob_get_clean();

        $this->tcpdf->writeHTML($html, true, false, true, false, '');
        
        $this->drawSignatures();
        
        return $this->tcpdf->Output('leccionario_' . $leccionario['id'] . '.pdf', 'S');
    }
    
    private function drawSignatures(): void
    {
        $pageWidth = $this->tcpdf->getPageWidth();
        $marginLeft = 15;
        
        $contentWidth = $pageWidth - $marginLeft * 2;
        $cellWidth = ($contentWidth - 2) / 2;
        
        $currentY = $this->tcpdf->GetY();
        
        $firmaSectionStart = $currentY - 50;
        
        if ($this->firmaDocente) {
            $this->tcpdf->Image('@' . $this->firmaDocente, $marginLeft + 2, $firmaSectionStart, $cellWidth - 4, 30, 'PNG');
        }
        
        if ($this->firmaRevisor) {
            $this->tcpdf->Image('@' . $this->firmaRevisor, $marginLeft + $cellWidth + 2, $firmaSectionStart, $cellWidth - 4, 30, 'PNG');
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
