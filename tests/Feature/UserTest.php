<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\EmailSenderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{

    private User $user;

    public function testUserNormal() {
        $this->user = new User();
        $this->user->firstname = "John";
        $this->user->lastname = "Doe";
        $this->user->birthday = Carbon::now()->subYears(20);
        $this->user->email = "test@email.com";
        $this->user->password = "passwordpassword";
        $this->assertTrue($this->user->isValid());
    }

    public function testUserMauvaisEmail() {
        $this->user = new User();
        $this->user->firstname = "John";
        $this->user->lastname = "Doe";
        $this->user->birthday = Carbon::now()->subYears(20);
        $this->user->email = "email.com"; // Email invalide
        $this->user->password = "passwordpassword";
        $this->assertFalse($this->user->isValid());
    }
    public function testUserPasswordTropCourt() {
        $this->user = new User();
        $this->user->firstname = "John";
        $this->user->lastname = "Doe";
        $this->user->birthday = Carbon::now()->subYears(20);
        $this->user->email = "test@email.com";
        $this->user->password = "pwd"; // Password < 8 caractères
        $this->assertFalse($this->user->isValid());
    }
    public function testUserPasswordTropLong() {
        $this->user = new User();
        $this->user->firstname = "John";
        $this->user->lastname = "Doe";
        $this->user->birthday = Carbon::now()->subYears(20);
        $this->user->email = "test@email.com";
        $this->user->password = "passwordpasswordpasswordpasswordpasswordpasswordpasswordpassword"; // Password > 40 caractères
        $this->assertFalse($this->user->isValid());
    }
    public function testUserTropJeune() {
        $this->user = new User();
        $this->user->firstname = "John";
        $this->user->lastname = "Doe";
        $this->user->birthday = Carbon::now()->subYears(12); // User a 12 ans
        $this->user->email = "test@email.com";
        $this->user->password = "passwordpassword";
        $this->assertFalse($this->user->isValid());
    }

}
