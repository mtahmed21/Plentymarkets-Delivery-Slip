<?php

namespace Plugins\DeliverySlip\PdfGenerator;

use Plenty\Plugin\PDFGenerator\AbstractPdfGenerator;
use Plenty\Plugin\PDFGenerator\PdfDocument;

class DeliverySlipGenerator extends AbstractPdfGenerator
{
    public function generate(array $data): PdfDocument
    {
        // Implement PDF generation logic here
        // This method is required to implement as per AbstractPdfGenerator
    }
}
