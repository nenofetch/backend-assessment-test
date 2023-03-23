<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DebitCardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    public function testCustomerCanSeeAListOfDebitCards()
    {
        // get /debit-cards
        $debitCards = DebitCard::factory()->active()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->get('/api/debit-cards');

        $response->assertOk();

        $response->assertJsonStructure([
            '*' => [
                'id',
                'number',
                'type',
                'expiration_date'
            ]
        ]);

        $response->assertJsonFragment([
            'id' => $debitCards->id,
            'number' => $debitCards->number,
            'type' => $debitCards->type,
            'expiration_date' => $debitCards->expiration_date->format('Y-m-d H:i:s')
        ]);

        $this->assertDatabaseHas('debit_cards', [
            'id' => $debitCards->id,
            'number' => $debitCards->number,
            'type' => $debitCards->type,
            'expiration_date' => $debitCards->expiration_date->format('Y-m-d H:i:s')
        ]);
    }

    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers()
    {
        // get /debit-cards
        $debitCards = DebitCard::factory()->active()->create();

        $response = $this->get('/api/debit-cards');

        $response->assertOk();

        $response->assertJsonMissing([
            'id' => $debitCards->id,
            'number' => $debitCards->number,
            'type' => $debitCards->type
        ]);
    }

    public function testCustomerCanCreateADebitCard()
    {
        // post /debit-cards
        $response = $this->post('/api/debit-cards', [
            'type' => 'VISATEST'
        ]);

        $response->assertCreated();

        $response->assertJsonStructure([
            'id',
            'number',
            'type',
            'expiration_date'
        ]);

        $data = $response->json();

        $response->assertJsonFragment([
            'id' => $data['id'],
            'number' => $data['number'],
            'type' => $data['type']
        ]);

        $this->assertDatabaseHas('debit_cards', [
            'id' => $data['id'],
            'number' => $data['number'],
            'type' => $data['type']
        ]);
    }

    public function testCustomerCanSeeASingleDebitCardDetails()
    {
        // get api/debit-cards/{debitCard}
    }

    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        // get api/debit-cards/{debitCard}
    }

    public function testCustomerCanActivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
    }

    public function testCustomerCanDeactivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
    }

    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        // put api/debit-cards/{debitCard}
    }

    public function testCustomerCanDeleteADebitCard()
    {
        // delete api/debit-cards/{debitCard}
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        // delete api/debit-cards/{debitCard}
    }

    // Extra bonus for extra tests :)
}
