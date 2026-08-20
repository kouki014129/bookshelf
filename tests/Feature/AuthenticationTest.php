<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しい認証情報でログインできる(): void
    {
        $user = User::factory()->create([
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'yamada@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    public function test_誤ったパスワードではログインできない(): void
    {
        User::factory()->create([
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this
            ->from('/login')
            ->post('/login', [
                'email' => 'yamada@example.com',
                'password' => 'wrong-password',
            ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => '認証情報が記録と一致しません。',
        ]);
    }

    public function test_正しい入力内容で新規登録できる(): void
    {
        $response = $this->post('/register', [
            'name' => '山田光輝',
            'email' => 'yamada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => '山田光輝',
            'email' => 'yamada@example.com',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_名前が未入力の場合は新規登録できない(): void
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => '',
                'email' => 'yamada@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => '名前は必須です。',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'yamada@example.com',
        ]);
    }

    public function test_メールアドレスの形式が不正な場合は新規登録できない(): void
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => '山田光輝',
                'email' => 'invalid-email',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスの形式が正しくありません。',
        ]);
        $this->assertGuest();
    }

    public function test_パスワード確認が一致しない場合は新規登録できない(): void
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => '山田光輝',
                'email' => 'yamada@example.com',
                'password' => 'password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワード確認が一致しません。',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'yamada@example.com',
        ]);
    }

    public function test_登録済みメールアドレスでは新規登録できない(): void
    {
        User::factory()->create([
            'email' => 'yamada@example.com',
        ]);

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => '別のユーザー',
                'email' => 'yamada@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'このメールアドレスはすでに登録されています。',
        ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_ログイン済みユーザーはログイン画面にアクセスできない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/login');

        $response->assertRedirect('/');
    }

    public function test_ログイン済みユーザーは会員登録画面にアクセスできない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/register');

        $response->assertRedirect('/');
    }

    public function test_ログイン済みユーザーはログアウトできる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
