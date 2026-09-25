<?php

namespace App\Services;

use TCPDF;

final class PdfTemplateRenderer implements TemplateRendererInterface
{
    public function render(array $templateVersion, array $data): array
    {
        $html = (new HtmlTemplateRenderer())->render($templateVersion, $data)['content'];
        $width = (float) ($templateVersion['page_width'] ?: 85);
        $height = (float) ($templateVersion['page_height'] ?: 54);
        $pdf = new TCPDF('P', 'mm', [$width, $height], true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        return ['format' => 'pdf', 'content' => $pdf->Output('', 'S'), 'mime' => 'application/pdf'];
    }
}
