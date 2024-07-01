<?php

namespace Plugins\DeliverySlip\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Order\Shipping\Information\Contracts\ShippingInformationRepositoryContract;
use Plenty\Modules\Order\Shipping\Package\Contracts\ShippingPackageRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\PDFGenerator\PdfGenerator;
use Plenty\Plugin\Log\Loggable;
use Plugins\DeliverySlip\Services\MockAPIService;

class DeliverySlipController extends Controller
{
    use Loggable;

    /**
     * @var ShippingInformationRepositoryContract
     */
    private $shippingInformationRepository;

    /**
     * @var ShippingPackageRepositoryContract
     */
    private $shippingPackageRepository;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var PdfGenerator
     */
    private $pdfGenerator;

    /**
     * @var MockAPIService
     */
    private $mockAPIService;

    public function __construct(
        ShippingInformationRepositoryContract $shippingInformationRepository,
        ShippingPackageRepositoryContract $shippingPackageRepository,
        ConfigRepository $configRepository,
        PdfGenerator $pdfGenerator,
        MockAPIService $mockAPIService
    ) {
        $this->shippingInformationRepository = $shippingInformationRepository;
        $this->shippingPackageRepository = $shippingPackageRepository;
        $this->configRepository = $configRepository;
        $this->pdfGenerator = $pdfGenerator;
        $this->mockAPIService = $mockAPIService;
    }

    /**
     * Create a delivery slip and handle label generation.
     *
     * @param Request $request
     * @param Twig $twig
     * @return string|\Plenty\Plugin\PDFGenerator\PdfDocument
     */
    public function createDeliverySlip(Request $request, Twig $twig)
    {
        $orderId = $request->get('orderId');
        $shipmentId = $request->get('shipmentId');
        $trackingCode = $request->get('trackingCode');

        // Update delivery order attributes
        $this->updateDeliveryOrderAttributes($orderId, $shipmentId, $trackingCode);

        // Generate delivery slip PDF
        return $this->generateDeliverySlipPDF($orderId, $shipmentId, $trackingCode, $twig);
    }

    /**
     * Handle the event when a delivery slip is created.
     *
     * @param array $eventData
     */
    public function handleDeliverySlipCreation(array $eventData)
    {
        $orderId = $eventData['orderId'];
        $shipmentId = $eventData['shipmentId'];
        $trackingCode = $eventData['trackingCode'];

        // Update delivery order attributes
        $this->updateDeliveryOrderAttributes($orderId, $shipmentId, $trackingCode);

        // Send data to appropriate endpoint based on configuration
        if ($this->configRepository->get('Plentymarkets-Delivery-Slip.label_generation')) {
            return $this->sendToLabelEndpoint($orderId);
        } else {
            return $this->sendToShipmentEndpoint($orderId);
        }
    }

    /**
     * Update delivery order attributes: Shipment ID and Tracking Code.
     *
     * @param int $orderId
     * @param string $shipmentId
     * @param string $trackingCode
     */
    private function updateDeliveryOrderAttributes(int $orderId, string $shipmentId, string $trackingCode)
    {
        // Update shipping information
        $shippingOrder = $this->shippingInformationRepository->getShippingInformationByOrderId($orderId);
        $shippingOrder->setAttribute('shipmentId', $shipmentId);
        $shippingOrder->setAttribute('trackingCode', $trackingCode);
        $this->shippingInformationRepository->updateShippingInformation($shippingOrder);

        // Update shipping packages
        $shippingPackages = $this->shippingPackageRepository->listShippingPackages($orderId);
        foreach ($shippingPackages as $shippingPackage) {
            $shippingPackage->setAttribute('shipmentId', $shipmentId);
            $shippingPackage->setAttribute('trackingCode', $trackingCode);
            $this->shippingPackageRepository->updateShippingPackage($shippingPackage);
        }
    }

    /**
     * Generate delivery slip PDF.
     *
     * @param int $orderId
     * @param string $shipmentId
     * @param string $trackingCode
     * @param Twig $twig
     * @return \Plenty\Plugin\PDFGenerator\PdfDocument
     */
    private function generateDeliverySlipPDF(int $orderId, string $shipmentId, string $trackingCode, Twig $twig)
    {
        // Get shipping status
        $shippingStatus = $this->shippingInformationRepository->getShippingInformationByOrderId($orderId)->status;

        // Render PDF template
        $pdfContent = $twig->render('@DeliverySlip::content.deliverySlip.twig', [
            'orderId' => $orderId,
            'shipmentId' => $shipmentId,
            'trackingCode' => $trackingCode,
            'shippingStatus' => $shippingStatus
        ]);

        // Generate PDF document
        return $this->pdfGenerator->generateFromHtml($pdfContent, [
            'format' => 'A4',
            'orientation' => 'portrait'
        ]);
    }

    /**
     * Send data to the /label endpoint.
     *
     * @param int $orderId
     * @return array
     */
    private function sendToLabelEndpoint(int $orderId)
    {
        $orderData = $this->mockAPIService->fetchOrderData($orderId);
        $response = $this->mockAPIService->sendToLabelEndpoint($orderData);
        
        // Download and add PDF to the order (mock implementation)
        $pdfUrl = 'https://example.com/delivery-slip.pdf'; // Static link for demonstration
        $this->addPdfToOrder($orderId, $pdfUrl);

        return $response;
    }

    /**
     * Send data to the /shipment endpoint.
     *
     * @param int $orderId
     * @return array
     */
    private function sendToShipmentEndpoint(int $orderId)
    {
        $orderData = $this->mockAPIService->fetchOrderData($orderId);
        $response = $this->mockAPIService->sendToShipmentEndpoint($orderData);

        return $response;
    }

    /**
     * Add PDF to the order.
     *
     * @param int $orderId
     * @param string $pdfUrl
     */
    private function addPdfToOrder(int $orderId, string $pdfUrl)
    {
        // Implement PDF addition to order logic (mock implementation)
        $this->getLogger(__METHOD__)->info('Downloading and adding PDF to order: ' . $pdfUrl);
        // Logic to download and save PDF to order
    }
}
