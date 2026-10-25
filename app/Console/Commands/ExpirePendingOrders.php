<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quét và hủy các đơn hàng chờ thanh toán đã quá hạn 15 phút, hoàn trả số lượng vé';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $this->info('Đang quét các đơn hàng chờ thanh toán đã quá hạn...');

        $expiredCount = $orderService->expireAllPendingOrders();

        $this->info("Đã xử lý hủy và hoàn vé thành công cho {$expiredCount} đơn hàng quá hạn.");

        return Command::SUCCESS;
    }
}
