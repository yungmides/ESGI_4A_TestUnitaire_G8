<?php

namespace Tests\Feature;

use App\Exceptions\AddItemTooEarlyException;
use App\Exceptions\ItemLimitExceededException;
use App\Exceptions\ItemNameAlreadyExistsException;
use App\Exceptions\MailNotSentException;
use App\Models\Item;
use App\Models\User;
use App\Services\EmailSenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    private EmailSenderService $emailSenderService;

    protected function setUp(): void
    {
        $this->emailSenderService = $this->getMockBuilder(EmailSenderService::class)->onlyMethods(['sendEmail'])->getMock();
        parent::setUp();
    }

    public function testAddItemNormal() {
        $user = User::factory()->has(Item::factory()->count(5))->create();
        $item = $user->items()->make();
        $item->name = "Test";
        $item->content = "Test";
        $item->created_at = $user->items()->latest("created_at")->first()->created_at->addHour();
        $user->add($item);

        $this->assertModelExists($item);
    }

    public function testAddItemMoins30Min() {
        $this->expectException(AddItemTooEarlyException::class);

        $user = User::factory()->has(Item::factory()->count(5))->create();
        $item = $user->items()->make();
        $item->name = "Test";
        $item->content = "Test";
        $item->created_at = $user->items()->latest("created_at")->first()->created_at; // Date de création de l'item égale à celle de l'item précédent
        $user->add($item);

    }

    public function testAddItemNameDuplicate() {
        $this->expectException(ItemNameAlreadyExistsException::class);

        $user = User::factory()->has(Item::factory()->count(5))->create();
        $item = $user->items()->make();
        $item->name = "Test";
        $item->content = "Test";
        $item->created_at = $user->items()->latest("created_at")->first()->created_at->addHour();
        $user->add($item);

        $sameNameItem = $user->items()->make();
        $sameNameItem->name = "Test"; // Même nom que l'item précédent
        $sameNameItem->content = "AUtre content";
        $sameNameItem->created_at = $item->created_at->addHour();
        $user->add($sameNameItem);

    }

    public function testLimitExceeded() {
        $this->expectException(ItemLimitExceededException::class);
        $user = User::factory()->has(Item::factory()->count(10))->create(); // Limite d'items atteinte
        $item = $user->items()->make();
        $item->name = "Test";
        $item->content = "Test";
        $item->created_at = $user->items()->latest("created_at")->first()->created_at->addHour();
        $user->add($item);
    }

    public function testMailNotSent() {
        $this->expectException(MailNotSentException::class);
        $this->emailSenderService->expects($this->any())->method("sendEmail")->willReturn(false); // Mailer échouera

        $user = User::factory()->has(Item::factory()->count(7))->create();
        $user->emailSenderService = $this->emailSenderService;
        $item = $user->items()->make();
        $item->name = "Test";
        $item->content = "Test";
        $item->created_at = $user->items()->latest("created_at")->first()->created_at->addHour();
        $user->add($item);

    }
}
