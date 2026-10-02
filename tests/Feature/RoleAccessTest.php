<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function member(): User
    {
        // Member::factory membuat User + baris members sekaligus — dashboard
        // member membaca $user->member dan error 500 bila barisnya tidak ada.
        return Member::factory()->create()->user;
    }

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/admin/books')->assertRedirect('/login');
        $this->get('/admin/transactions')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_member_area(): void
    {
        $this->get('/member/dashboard')->assertRedirect('/login');
        $this->get('/member/books')->assertRedirect('/login');
    }

    public function test_member_cannot_access_admin_area(): void
    {
        $this->actingAs($this->member())->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->member())->get('/admin/books')->assertForbidden();
        $this->actingAs($this->member())->get('/admin/members')->assertForbidden();
        $this->actingAs($this->member())->get('/admin/transactions')->assertForbidden();
        $this->actingAs($this->member())->get('/admin/penalties')->assertForbidden();
    }

    public function test_admin_cannot_access_member_area(): void
    {
        $this->actingAs($this->admin())->get('/member/dashboard')->assertForbidden();
        $this->actingAs($this->admin())->get('/member/books')->assertForbidden();
        $this->actingAs($this->admin())->get('/member/my-borrows')->assertForbidden();
        $this->actingAs($this->admin())->get('/member/my-penalties')->assertForbidden();
    }

    public function test_admin_can_access_admin_area(): void
    {
        $this->actingAs($this->admin())->get('/admin/dashboard')->assertOk();
        $this->actingAs($this->admin())->get('/admin/books')->assertOk();
        $this->actingAs($this->admin())->get('/admin/members')->assertOk();
        $this->actingAs($this->admin())->get('/admin/transactions')->assertOk();
        $this->actingAs($this->admin())->get('/admin/penalties')->assertOk();
    }

    public function test_member_can_access_member_area(): void
    {
        $this->actingAs($this->member())->get('/member/dashboard')->assertOk();
        $this->actingAs($this->member())->get('/member/books')->assertOk();
        $this->actingAs($this->member())->get('/member/my-borrows')->assertOk();
        $this->actingAs($this->member())->get('/member/my-penalties')->assertOk();
    }

    public function test_root_redirects_guest_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_dashboard_dispatches_by_role(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertRedirect('/admin/dashboard');
        $this->actingAs($this->member())->get('/dashboard')->assertRedirect('/member/dashboard');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_member_can_view_book_detail_but_not_admin_edit(): void
    {
        $user = $this->member();
        $book = Book::factory()->create();

        $this->actingAs($user)->get('/member/books/'.$book->id)->assertOk();
        $this->actingAs($user)->get('/admin/books/'.$book->id.'/edit')->assertForbidden();
    }
}
