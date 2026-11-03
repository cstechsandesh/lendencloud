<?php

namespace App\Models;

use App\Core\CSModel;

class PaymentTransactionModel extends CSModel
{
    protected $table            = 'payment_transactions';
    protected $primaryKey       = 'transaction_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'transaction_company_id',
        'transaction_user_id',
        'transaction_type',
        'transaction_reference_id',
        'transaction_gateway',
        'transaction_order_id',
        'transaction_payment_id',
        'transaction_signature',
        'transaction_amount',
        'transaction_currency',
        'transaction_status',
        'transaction_billing_cycle',
        'transaction_payment_method',
        'transaction_notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get transactions for a given company with user & plan info
     */
    public function getCompanyTransactions(int $companyId, int $limit = 20): array
    {
        return $this->select('payment_transactions.*, users.user_name, users.user_email, subscription_plans.plan_name')
            ->join('users', 'users.user_id = payment_transactions.transaction_user_id', 'left')
            ->join('subscription_plans', 'subscription_plans.plan_id = payment_transactions.transaction_reference_id', 'left')
            ->where('payment_transactions.transaction_company_id', $companyId)
            ->orderBy('payment_transactions.transaction_id', 'DESC')
            ->findAll($limit);
    }

    /**
     * Get a transaction by its Razorpay Order ID
     */
    public function findByOrderId(string $orderId): ?array
    {
        return $this->where('transaction_order_id', $orderId)->first();
    }
}

