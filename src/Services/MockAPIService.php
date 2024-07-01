<?php

namespace Plugins\DeliverySlip\Services;

class MockAPIService
{
    /**
     * Fetch order data from a mock API.
     *
     * @param int $orderId
     * @return array
     */
    public function fetchOrderData(int $orderId): array
    {
        // Implement logic to fetch order data from mock API
        return [
            'orderId' => $orderId, // Example data
            // Include other relevant order data
        ];
    }

    /**
     * Send order data to the /label endpoint of mock API.
     *
     * @param array $orderData
     * @return array
     */
    public function sendToLabelEndpoint(array $orderData): array
    {
        // Implement logic to send data to /label endpoint of mock API
        return [
            'shipmentId' => '123', // Example response data
            'trackingCode' => 'ABC123',
            'pdfUrl' => 'https://example.com/delivery-slip.pdf',
        ];
    }

    /**
     * Send order data to the /shipment endpoint of mock API.
     *
     * @param array $orderData
     * @return array
     */
    public function sendToShipmentEndpoint(array $orderData): array
    {
        // Implement logic to send data to /shipment endpoint of mock API
        return [
            'shipmentId' => '123', // Example response data
            'trackingCode' => 'ABC123',
        ];
    }
}
