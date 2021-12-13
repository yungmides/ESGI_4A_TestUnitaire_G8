<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\EmailSenderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{
    private User $user;
    private EmailSenderService $emailSenderService;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->user->firstname = "John";
        $this->user->lastname = "Doe";
        $this->user->birthday = Carbon::now()->subYears(20);
        $this->user->email = "test@email.com";
        $this->user->password = "passwordpassword";

        $this->emailSenderService = $this->getMockBuilder(EmailSenderService::class)->onlyMethods(['sendEmail'])->getMock();
        parent::setUp();
    }
}
