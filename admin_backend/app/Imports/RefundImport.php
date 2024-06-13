<?php

namespace App\Imports;

use App\Models\Refund;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RefundImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     * @return bool
     */
    public function collection(Collection $collection): bool
    {
        try {
            foreach ($collection as $row)
            {
                Refund::create([
                    'order_id' => $row['order_id'],
                    'user_id' => $row['user_id'],
                    'status' => $row['status'],
                    'message_seller' => $row['message_seller'],
                    'message_user' => $row['message_user'],
                ]);
            }
        }catch (\Exception $exception)
        {
            dd($exception);
        }

        return true;
    }

    public function headingRow(): int
    {
        return 1;
    }
}
