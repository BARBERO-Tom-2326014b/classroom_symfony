<?php

namespace App\Service;

use Smalot\PdfParser\Parser;

final class PdfTextExtractor
{
    public function __construct(private Parser $parser = new Parser())
    {
    }

    /**
     * Extrait le texte brut d'un PDF.
     *
     * @return string Texte extrait (peut être vide).
     */
    public function extract(string $pdfPath): string
    {
        if (!is_file($pdfPath) || !is_readable($pdfPath)) {
            return '';
        }

        try {
            $pdf = $this->parser->parseFile($pdfPath);
            return trim((string) $pdf->getText());
        } catch (\Throwable) {
            return '';
        }
    }
}
