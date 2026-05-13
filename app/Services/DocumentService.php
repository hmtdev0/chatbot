<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class DocumentService
{
    public function readAllDocuments(): string
    {
        $text = '';

        $folder = storage_path('app/knowledge');

        $files = glob($folder.'/*');

        foreach ($files as $file) {

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // PDF
            if ($extension === 'pdf') {

                $parser = new Parser;

                $pdf = $parser->parseFile($file);

                $text .= "\n".$pdf->getText();
            }

            // DOCX
            elseif ($extension === 'docx') {

                $phpWord = IOFactory::load($file);

                foreach ($phpWord->getSections() as $section) {

                    foreach ($section->getElements() as $element) {

                        if (method_exists($element, 'getText')) {

                            $text .= "\n".$element->getText();
                        }
                    }
                }
            }

            // TXT
            elseif ($extension === 'txt') {

                $text .= "\n".file_get_contents($file);
            }
        }

        return $text;
    }
}
