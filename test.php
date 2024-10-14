<?php
// test.php

require_once __DIR__ . '/vendor/autoload.php'; // Autoload FPDF über Composer

use FPDF;

// FPDF Test mit einfacher Ausgabe
class TestPDF extends FPDF
{
    // Kopfzeile
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'FPDF Testbericht', 0, 1, 'C');
        $this->Ln(10);
    }

    // Fußzeile
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Seite ' . $this->PageNo(), 0, 0, 'C');
    }

    // Beispielinhalt
    function BasicTable()
    {
        $this->SetFont('Arial', '', 12);
        $this->Cell(40, 10, 'Name', 1);
        $this->Cell(40, 10, 'Firma', 1);
        $this->Cell(40, 10, 'Besuchsgrund', 1);
        $this->Ln();

        $this->Cell(40, 10, 'Max Mustermann', 1);
        $this->Cell(40, 10, 'Musterfirma GmbH', 1);
        $this->Cell(40, 10, 'Meeting', 1);
        $this->Ln();
    }
}

// Erzeugen eines PDFs
$pdf = new TestPDF();
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output('D', 'Testbericht.pdf');
