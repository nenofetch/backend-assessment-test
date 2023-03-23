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
        $debitCards = DebitCard::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/api/debit-cards/' . $debitCards->id);

        $response->assertOk();

        $response->assertJsonStructure([
            'id',
            'number',
            'type',
            'type',
            'expiration_date'
        ]);

        $response->assertJsonFragment([
            'id' => $debitCards->id,
            'number' => $debitCards->number,
            'type' => $debitCards->number,
            'expiration_date' => $debitCards->expiration_date->format('Y-m-d H:i:s')
        ]);

        $this->assertDatabaseHas('debit_cards', [
            'id' => $debitCards->id,
            'number' => $debitCards->number,
            'type' => $debitCards->number,
            'expiration_date' => $debitCards->expiration_date->format('Y-m-d H:i:s')
        ]);
    }

    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        // get api/debit-cards/{debitCard}
        $debitCards = DebitCard::factory()->active()->create();

        $response = $this->get('/api/debit-cards', $debitCards->id);
        $response->assertForbidden();
    }

    public function testCustomerCanActivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
        $debitCards = DebitCard::factory()->active()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->put('/api/debit-cards' . $debitCards->id, [
            'is_active' => true,
        ]);

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $debitCards->id,
            'is_active' => true
        ]);

        $this->assertDatabaseHas('debit_cards', [

            'id' => $debitCards->id,
            'disabled_at' => null,
        ]);
    }

    public function testCustomerCanDeactivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
        $debitCards = DebitCard::factory()->active()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->put('/api/debit-cards' . $debitCards->id, [
            'is_active' => false,
        ]);

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $debitCards->id,
            'is_active' => false
        ]);

        $this->assertDatabaseHas('debit_cards', [

            'id' => $debitCards->id,
            'disabled_at' => now(),
        ]);
    }

    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        // put api/debit-cards/{debitCard}
        $debitCards = DebitCard::factory()->active()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->put('/api/debit-cards' . $debitCards->id, [
            'is_active' => 'testing123',
        ]);

        $response->assertSessionHasErrors('is_active');

        $this->assertDatabaseHas('debit_cards', [

            'id' => $debitCards->id,
            'disabled_at' => null,
        ]);
    }

    public function testCustomerCanDeleteADebitCard()
    {
        // delete api/debit-cards/{debitCard}
        $debitCards = DebitCard::factory()->active()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->delete('/api/debit-cards' . $debitCards->id);

        $response->assertSuccessful();

        $this->assertDatabaseHas('debit_cards', [

            'id' => $debitCards->id,
            'deleted_at' => $debitCards->fresh()->deleted_at
        ]);
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        // delete api/debit-cards/{debitCard}
    }

    // Extra bonus for extra tests :)
}
