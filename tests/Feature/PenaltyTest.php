<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenaltyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function unpaidPenalty(): Penalty
    {
        $transaction = Transaction::factory()->returnedLate(3)->create();

        // Pin nilainya supaya perhitungan total bisa diprediksi (3 × 2000 = 6000).
        return Penalty::factory()->create([
            'transaction_id' => $transaction->id,
            'member_id' => $transaction->member_id,
            'days_late' => 3,
            'fine_amount' => 6000,
        ]);
    }

    public function test_admin_can_mark_penalty_as_paid(): void
    {
        $penalty = $this->unpaidPenalty();

        $response = $this->actingAs($this->admin)
            ->put('/admin/penalties/'.$penalty->id.'/pay');

        $response->assertRedirect('/admin/penalties');

        $penalty->refresh();
        $this->assertSame('paid', $penalty->status);
        $this->assertNotNull($penalty->paid_date);
        $this->assertTrue($penalty->isPaid());
    }

    public function test_paying_an_already_paid_penalty_is_rejected(): void
    {
        $penalty = Penalty::factory()->paid()->create();

        $response = $this->actingAs($this->admin)
            ->put('/admin/penalties/'.$penalty->id.'/pay');

        $response->assertSessionHas('error');
        $this->assertTrue($penalty->refresh()->isPaid());
    }

    public function test_pay_form_is_rendered_for_unpaid_penalty(): void
    {
        $penalty = $this->unpaidPenalty();

        $this->actingAs($this->admin)
            ->get('/admin/penalties/'.$penalty->id.'/pay')
            ->assertOk()
            ->assertViewIs('admin.penalties.pay');
    }

    public function test_pay_form_redirects_when_penalty_already_paid(): void
    {
        $penalty = Penalty::factory()->paid()->create();

        $this->actingAs($this->admin)
            ->get('/admin/penalties/'.$penalty->id.'/pay')
            ->assertRedirect('/admin/penalties');
    }

    public function test_penalty_index_shows_paid_and_unpaid_totals(): void
    {
        $this->unpaidPenalty(); // 3 hari × 2000 = 6000
        Penalty::factory()->count(2)->paid()->create([
            'days_late' => 2,
            'fine_amount' => 4000,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/penalties')
            ->assertOk()
            ->assertViewHas('totalUnpaid', 6000)
            ->assertViewHas('totalPaid', 8000);
    }

    public function test_member_only_sees_their_own_penalties(): void
    {
        $memberUser = User::factory()->create(['role' => 'member']);
        $myMember = Member::factory()->for($memberUser, 'user')->create();

        $myLate = Transaction::factory()->for($myMember)->returnedLate(2)->create();
        $mine = Penalty::factory()->create([
            'transaction_id' => $myLate->id,
            'member_id' => $myMember->id,
        ]);
        $other = $this->unpaidPenalty();

        $response = $this->actingAs($memberUser)->get('/member/my-penalties');

        $response->assertOk()->assertViewHas('penalties', function ($penalties) use ($mine, $other) {
            $ids = $penalties->pluck('id');

            return $ids->contains($mine->id) && ! $ids->contains($other->id);
        });
    }

    public function test_member_dashboard_shows_unpaid_fine_total(): void
    {
        $transaction = Transaction::factory()->returnedLate(3)->create();
        $member = $transaction->member;
        Penalty::factory()->create([
            'transaction_id' => $transaction->id,
            'member_id' => $member->id,
            'days_late' => 3,
            'fine_amount' => 6000,
        ]);

        $this->actingAs($member->user)
            ->get('/member/dashboard')
            ->assertOk()
            ->assertViewHas('unpaidPenalty', 6000);
    }
}
