<?php
class Transaction{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly float $amount,
    ){
    }

    public function getId(): int{
        return $this->id;
    }
    
    public function getType(): string
    {
        return $this->type;
    }
 
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function process(float &$balance): array
    {
        return match ($this->type) {
            'deposit' => $this->processDeposit($balance),
            'withdrawal' => $this->processWithdrawal($balance),
            default => [
                'success' => false,
                'message' => 'Jenis transaksi tidak dikenali.',
            ],
        };
    }
    private function processDeposit(float &$balance): array
    {
        $balance += $this->amount;
 
        return [
            'success' => true,
            'message' => sprintf('Deposit sebesar %.2f berhasil diproses.', $this->amount),
        ];
    }
 
    private function processWithdrawal(float &$balance): array
    {
        if ($this->amount > $balance) {
            return [
                'success' => false,
                'message' => 'Penarikan gagal: saldo tidak mencukupi.',
            ];
        }
 
        $balance -= $this->amount;
 
        return [
            'success' => true,
            'message' => sprintf('Penarikan sebesar %.2f berhasil diproses.', $this->amount),
        ];
    }
}