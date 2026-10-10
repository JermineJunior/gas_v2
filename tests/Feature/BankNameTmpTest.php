<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Employee;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankNameTmpTest extends TestCase
{
    use RefreshDatabase;

    private function seedDeposit(): array
    {
        $station = Station::factory()->create();
        $employee = Employee::create(['name' => 'emp', 'station_id' => $station->id]);
        $deposit = Deposit::create([
            'station_id' => $station->id,
            'employee_id' => $employee->id,
            'date' => '2026-10-10',
            'total_old_machine' => 0,
            'total_new_machine' => 0,
            'remaining' => 0,
            'expenses_total' => 0,
        ]);
        $detail = DepositDetail::create([
            'date' => '2026-10-10',
            'deposit_id' => $deposit->id,
            'station_id' => $station->id,
            'deposit_amount' => 100,
            'deposit_desc' => 'old desc',
            'status' => 0,
        ]);

        return [$station, $employee, $deposit, $detail];
    }

    // هذا التطبيق بلا عمود email في جدول users، فالمصنع الافتراضي لا يصلح هنا
    private function makeUser(): User
    {
        return User::create([
            'name'     => 'Tester',
            'username' => 'tester_' . uniqid(),
            'password' => 'secret',
            'type'     => 1,
        ]);
    }

    public function test_store_saves_bank_name_and_operation_number(): void
    {
        [$station, $employee] = $this->seedDeposit();
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('deposit_detail.store'), [
            'station_id' => $station->id,
            'employee_id' => $employee->id,
            'date' => '2026-10-10',
            'deposit_amount' => ['250'],
            'deposit_desc' => ['desc'],
            'bank_name' => ['الاهلي'],
            'operation_number' => ['OP-123'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('deposit_details', [
            'deposit_amount' => '250',
            'bank_name' => 'الاهلي',
            'operation_number' => 'OP-123',
        ]);
    }

    public function test_store_allows_missing_bank_fields(): void
    {
        [$station, $employee] = $this->seedDeposit();
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('deposit_detail.store'), [
            'station_id' => $station->id,
            'employee_id' => $employee->id,
            'date' => '2026-10-10',
            'deposit_amount' => ['250'],
            'deposit_desc' => ['desc'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('deposit_details', ['deposit_amount' => '250', 'bank_name' => null]);
    }

    public function test_update_persists_new_bank_values_on_pending_line(): void
    {
        [, , $deposit, $detail] = $this->seedDeposit();
        $user = $this->makeUser();

        $this->actingAs($user)->put(route('deposit_detail.update', $deposit->id), [
            'date' => '2026-10-10',
            'detail_ids' => [$detail->id],
            'deposit_amount' => ['100'],
            'deposit_desc' => ['old desc'],
            'bank_name' => ['CIB'],
            'operation_number' => ['OP-9'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('deposit_details', ['id' => $detail->id, 'bank_name' => 'CIB', 'operation_number' => 'OP-9']);
    }

    public function test_update_stores_null_when_bank_values_cleared(): void
    {
        [, , $deposit, $detail] = $this->seedDeposit();
        $detail->update(['bank_name' => 'CIB', 'operation_number' => 'OP-9']);
        $user = $this->makeUser();

        $this->actingAs($user)->put(route('deposit_detail.update', $deposit->id), [
            'date' => '2026-10-10',
            'detail_ids' => [$detail->id],
            'deposit_amount' => ['100'],
            'deposit_desc' => ['old desc'],
            'bank_name' => ['   '],
            'operation_number' => [''],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('deposit_details', ['id' => $detail->id, 'bank_name' => null, 'operation_number' => null]);
    }

    public function test_update_blocks_editing_bank_name_on_approved_line(): void
    {
        [, , $deposit, $detail] = $this->seedDeposit();
        $detail->update(['status' => 1, 'bank_name' => 'CIB', 'operation_number' => 'OP-9']);
        $user = $this->makeUser();

        $this->actingAs($user)->put(route('deposit_detail.update', $deposit->id), [
            'date' => '2026-10-10',
            'detail_ids' => [$detail->id],
            'deposit_amount' => ['100'],
            'deposit_desc' => ['old desc'],
            'bank_name' => ['مصرف آخر'],
            'operation_number' => ['OP-9'],
        ])->assertSessionHasErrors();

        $this->assertDatabaseHas('deposit_details', ['id' => $detail->id, 'bank_name' => 'CIB']);
    }

    public function test_update_allows_unchanged_approved_line_with_bank_values(): void
    {
        [, , $deposit, $detail] = $this->seedDeposit();
        $detail->update(['status' => 1, 'bank_name' => 'CIB', 'operation_number' => 'OP-9']);
        $user = $this->makeUser();

        $this->actingAs($user)->put(route('deposit_detail.update', $deposit->id), [
            'date' => '2026-10-10',
            'detail_ids' => [$detail->id],
            'deposit_amount' => ['100'],
            'deposit_desc' => ['old desc'],
            'bank_name' => ['CIB'],
            'operation_number' => ['OP-9'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('deposit_details', ['id' => $detail->id, 'status' => 1, 'bank_name' => 'CIB']);
    }
}
