<?php
/**
 * Classe para integração com API do Asaas
 * Documentação: https://docs.asaas.com/
 */
class AsaasAPI
{
    private $apiKey;
    private $baseUrl;
    private $environment; // 'sandbox' ou 'production'

    public function __construct($apiKey, $environment = 'sandbox')
    {
        $this->apiKey = $apiKey;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';
    }

    /**
     * Requisição genérica à API
     */
    private function request($endpoint, $method = 'GET', $data = null)
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new Exception($result['errors'][0]['description'] ?? 'Erro na API Asaas');
        }

        return $result;
    }

    /**
     * Criar cobrança (fatura)
     */
    public function createPayment($data)
    {
        return $this->request('/payments', 'POST', $data);
    }

    /**
     * Buscar cobrança
     */
    public function getPayment($paymentId)
    {
        return $this->request('/payments/' . $paymentId, 'GET');
    }

    /**
     * Atualizar cobrança
     */
    public function updatePayment($paymentId, $data)
    {
        return $this->request('/payments/' . $paymentId, 'PUT', $data);
    }

    /**
     * Deletar cobrança
     */
    public function deletePayment($paymentId)
    {
        return $this->request('/payments/' . $paymentId, 'DELETE');
    }

    /**
     * Criar transferência (saque)
     */
    public function createTransfer($data)
    {
        return $this->request('/transfers', 'POST', $data);
    }

    /**
     * Buscar transferência
     */
    public function getTransfer($transferId)
    {
        return $this->request('/transfers/' . $transferId, 'GET');
    }

    /**
     * Listar transferências
     */
    public function listTransfers($filters = [])
    {
        $query = http_build_query($filters);
        return $this->request('/transfers?' . $query, 'GET');
    }

    /**
     * Criar cliente no Asaas
     */
    public function createCustomer($data)
    {
        return $this->request('/customers', 'POST', $data);
    }

    /**
     * Buscar cliente
     */
    public function getCustomer($customerId)
    {
        return $this->request('/customers/' . $customerId, 'GET');
    }

    /**
     * Atualizar cliente
     */
    public function updateCustomer($customerId, $data)
    {
        return $this->request('/customers/' . $customerId, 'PUT', $data);
    }

    /**
     * Obter saldo da conta
     */
    public function getBalance()
    {
        return $this->request('/finance/balance', 'GET');
    }

    /**
     * Gerar QR Code PIX
     */
    public function getPixQrCode($paymentId)
    {
        return $this->request('/payments/' . $paymentId . '/pixQrCode', 'GET');
    }

    /**
     * Webhook - Verificar assinatura
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        // Implementar verificação de assinatura se necessário
        return true;
    }
}

// Configuração global
require_once __DIR__ . '/../config/env_loader.php';

$ASAAS_API_KEY = getenv('ASAAS_API_KEY') ?: '';
$ASAAS_ENV = getenv('ASAAS_ENV') ?: 'sandbox';

function getAsaasAPI()
{
    global $ASAAS_API_KEY, $ASAAS_ENV;
    return new AsaasAPI($ASAAS_API_KEY, $ASAAS_ENV);
}
?>