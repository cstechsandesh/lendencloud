<?php

namespace App\Controllers\Payments;

use App\Controllers\BaseController;
use App\Libraries\RazorpayService;
use App\Models\PaymentTransactionModel;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;

class RazorpayController extends BaseController
{
    protected RazorpayService $razorpay;
    protected PaymentTransactionModel $txModel;
    protected SubscriptionModel $subModel;
    protected SubscriptionPlanModel $planModel;

    public function __construct()
    {
        $this->razorpay  = new RazorpayService();
        $this->txModel   = new PaymentTransactionModel();
        $this->subModel  = new SubscriptionModel();
        $this->planModel = new SubscriptionPlanModel();
    }

    /**
     * Create a Razorpay Order for Subscription Upgrade or Renewal
     */
    public function createSubscriptionOrder()
    {
        $companyId = $this->getCompanyId();
        if (!$companyId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'error'   => 'Active company session required.',
            ]);
        }

        $subscriptionsConfig  = config('Subscriptions');
        $subscriptionsEnabled = (bool)($subscriptionsConfig->enabled ?? true);
        if (!$subscriptionsEnabled) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'error'   => 'Self-service online renewals are temporarily paused by administration.',
            ]);
        }

        $planId = (int)$this->request->getPost('plan_id');
        $cycle  = trim($this->request->getPost('billing_cycle') ?? 'monthly');
        if (!in_array($cycle, ['monthly', 'yearly'], true)) {
            $cycle = 'monthly';
        }

        $plan = $this->planModel->find($planId);
        if (!$plan || empty($plan['plan_is_active'])) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'error'   => 'Selected subscription tier is invalid or inactive.',
            ]);
        }

        $amount = ($cycle === 'yearly') ? (float)$plan['plan_price_per_year'] : (float)$plan['plan_price_per_month'];
        if ($amount <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error'   => 'Selected tier is complimentary or free. No online payment required.',
            ]);
        }

        $receipt = 'RCPT_SUB_' . $companyId . '_' . $planId . '_' . time();
        $notes   = [
            'company_id'    => $companyId,
            'plan_id'       => $planId,
            'plan_name'     => $plan['plan_name'],
            'billing_cycle' => $cycle,
            'user_id'       => (int)($this->session->get('user_id') ?? 0),
        ];

        $orderResult = $this->razorpay->createOrder($amount, $receipt, $notes);

        if (empty($orderResult['success'])) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error'   => $orderResult['error'] ?? 'Failed to initialize payment gateway order.',
            ]);
        }

        // Record pending transaction
        $txId = $this->txModel->insert([
            'transaction_company_id'    => $companyId,
            'transaction_user_id'       => (int)($this->session->get('user_id') ?? 0),
            'transaction_type'          => 'subscription_upgrade',
            'transaction_reference_id'  => $planId,
            'transaction_gateway'       => 'razorpay',
            'transaction_order_id'      => $orderResult['order_id'],
            'transaction_amount'        => $amount,
            'transaction_currency'      => $orderResult['currency'],
            'transaction_status'        => 'pending',
            'transaction_billing_cycle' => $cycle,
            'transaction_notes'         => json_encode([
                'plan_name' => $plan['plan_name'],
                'receipt'   => $receipt,
                'simulated' => $orderResult['simulated'] ?? false,
            ]),
        ]);

        $simulatedPaymentId = 'pay_test_' . substr(bin2hex(random_bytes(6)), 0, 10);
        $testSignature      = $this->razorpay->generateTestSignature($orderResult['order_id'], $simulatedPaymentId);

        return $this->response->setJSON([
            'success'               => true,
            'order_id'              => $orderResult['order_id'],
            'amount'                => $orderResult['amount'],
            'amount_formatted'      => '₹' . number_format($amount, 2),
            'currency'              => $orderResult['currency'],
            'key_id'                => $this->razorpay->getKeyId(),
            'merchant_name'         => $this->razorpay->getMerchantName(),
            'plan_id'               => $planId,
            'plan_name'             => $plan['plan_name'],
            'billing_cycle'         => $cycle,
            'is_test_mode'          => $this->razorpay->isTestMode(),
            'is_simulated'          => $orderResult['simulated'] ?? false,
            'test_payment_id'       => $simulatedPaymentId,
            'test_signature'        => $testSignature,
            'transaction_id'        => $txId,
            'prefill'               => [
                'name'    => $this->session->get('user_name') ?? '',
                'email'   => $this->session->get('user_email') ?? '',
                'contact' => $this->session->get('user_phone') ?? '',
            ],
            'notes'                 => $notes,
        ]);
    }

    /**
     * Verify payment signature from Razorpay Checkout and activate plan
     */
    public function verifySubscriptionPayment()
    {
        $companyId = $this->getCompanyId();
        if (!$companyId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'error'   => 'Session expired. Please log in again.',
            ]);
        }

        $orderId       = trim($this->request->getPost('razorpay_order_id') ?? '');
        $paymentId     = trim($this->request->getPost('razorpay_payment_id') ?? '');
        $signature     = trim($this->request->getPost('razorpay_signature') ?? '');
        $transactionId = (int)$this->request->getPost('transaction_id');

        if (empty($orderId) || empty($paymentId)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error'   => 'Missing required Razorpay payment reference parameters.',
            ]);
        }

        // Locate transaction record
        $tx = null;
        if ($transactionId > 0) {
            $tx = $this->txModel->find($transactionId);
        }
        if (!$tx) {
            $tx = $this->txModel->findByOrderId($orderId);
        }

        if (!$tx || (int)$tx['transaction_company_id'] !== $companyId) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'error'   => 'Transaction record not found for this account.',
            ]);
        }

        if ($tx['transaction_status'] === 'successful') {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'This payment has already been verified and processed.',
                'redirect' => base_url('subscription'),
            ]);
        }

        // Signature Verification
        $isValid = $this->razorpay->verifyPaymentSignature($orderId, $paymentId, $signature);
        if (!$isValid) {
            $this->txModel->update($tx['transaction_id'], [
                'transaction_status' => 'failed',
                'transaction_payment_id' => $paymentId,
                'transaction_signature'  => $signature,
                'transaction_notes'      => 'Failed signature verification',
            ]);

            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error'   => 'Payment signature verification failed. The payment could not be authenticated.',
            ]);
        }

        // Fetch payment method info if available
        $payMethod = 'razorpay';
        $paymentDetails = $this->razorpay->fetchPayment($paymentId);
        if ($paymentDetails && !empty($paymentDetails['method'])) {
            $payMethod = 'razorpay_' . $paymentDetails['method'];
        } elseif (str_starts_with($paymentId, 'pay_test_')) {
            $payMethod = 'razorpay_test_card';
        }

        // 1. Mark transaction successful
        $this->txModel->update($tx['transaction_id'], [
            'transaction_status'         => 'successful',
            'transaction_payment_id'     => $paymentId,
            'transaction_signature'      => $signature,
            'transaction_payment_method' => $payMethod,
        ]);

        // 2. Upgrade / Extend company subscription
        $planId = (int)$tx['transaction_reference_id'];
        $cycle  = $tx['transaction_billing_cycle'] ?: 'monthly';
        $amount = (float)$tx['transaction_amount'];

        $currentSub = $this->subModel->getCompanySubscription($companyId);
        $periodAdd  = ($cycle === 'yearly') ? '+1 year' : '+1 month';

        // Extend from current expiration date if active & unexpired, else from today
        $startDate = date('Y-m-d');
        if (!empty($currentSub['subscription_end_date']) && strtotime($currentSub['subscription_end_date']) > time()) {
            $endDate = date('Y-m-d', strtotime($currentSub['subscription_end_date'] . ' ' . $periodAdd));
        } else {
            $endDate = date('Y-m-d', strtotime($periodAdd));
        }

        if ($currentSub && !empty($currentSub['subscription_id'])) {
            $this->subModel->update($currentSub['subscription_id'], [
                'subscription_plan_id'        => $planId,
                'subscription_billing_cycle'  => $cycle,
                'subscription_amount'         => $amount,
                'subscription_start_date'     => $startDate,
                'subscription_end_date'       => $endDate,
                'subscription_status'         => 'active',
                'subscription_payment_method' => $payMethod,
            ]);
        } else {
            $this->subModel->insert([
                'subscription_company_id'     => $companyId,
                'subscription_plan_id'        => $planId,
                'subscription_billing_cycle'  => $cycle,
                'subscription_amount'         => $amount,
                'subscription_start_date'     => $startDate,
                'subscription_end_date'       => $endDate,
                'subscription_status'         => 'active',
                'subscription_payment_method' => $payMethod,
            ]);
        }

        $plan = $this->planModel->find($planId);
        $planName = $plan['plan_name'] ?? 'Selected Tier';

        $this->session->setFlashdata('success', "Payment of ₹" . number_format($amount, 2) . " via Razorpay confirmed! Your subscription has been upgraded to {$planName} (valid until " . date('d M Y', strtotime($endDate)) . ").");

        return $this->response->setJSON([
            'success'   => true,
            'message'   => "Payment successful! Upgraded to {$planName}.",
            'plan_name' => $planName,
            'end_date'  => date('d M Y', strtotime($endDate)),
            'redirect'  => base_url('subscription'),
        ]);
    }

    /**
     * Log payment failure or cancellation
     */
    public function paymentFailed()
    {
        $companyId = $this->getCompanyId();
        $orderId   = trim($this->request->getPost('order_id') ?? '');
        $reason    = trim($this->request->getPost('reason') ?? 'User cancelled or gateway transaction failed');

        if ($orderId && $companyId) {
            $tx = $this->txModel->findByOrderId($orderId);
            if ($tx && (int)$tx['transaction_company_id'] === $companyId && $tx['transaction_status'] === 'pending') {
                $this->txModel->update($tx['transaction_id'], [
                    'transaction_status' => 'failed',
                    'transaction_notes'  => $reason,
                ]);
            }
        }

        return $this->response->setJSON(['success' => true]);
    }
}

