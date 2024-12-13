<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChirpTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    // Exercice 1
    public function test_un_utilisateur_peut_creer_un_chirp()
    {
        // Simuler un utilisateur connecté
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);
        // Envoyer une requête POST pour créer un chirp
        $reponse = $this->post('/chirps', [
            'message' => 'Mon premier chirp !'
        ]);
        // Vérifier que le chirp a été ajouté à la base de données
        $reponse->assertStatus(302);
        $this->assertDatabaseHas('chirps', [
            'message' => 'Mon premier chirp !',
            'user_id' => $utilisateur->id,
        ]);
    }
//exercice 2
    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
    {
    $utilisateur = User::factory()->create();
    $this->actingAs($utilisateur);
    $reponse = $this->post('/chirps', [
   
   'message' => ''
    ]);
    $reponse->assertSessionHasErrors(['message']);
    }
    public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
    {
    $utilisateur = User::factory()->create();
    $this->actingAs($utilisateur);
    $reponse = $this->post('/chirps', [
    'message' => str_repeat('a', 256)
    ]);
    $reponse->assertSessionHasErrors(['message']);
    }

    //exercice 4 
    public function test_un_utilisateur_peut_modifier_son_chirp()
    {
        $utilisateur = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

        $this->actingAs($utilisateur);

        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => 'Chirp modifié',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('chirps', [
            'id' => $chirp->id,
            'message' => 'Chirp modifié',
        ]);
    }
   
// exercie 5
    public function test_un_utilisateur_peut_supprimer_son_chirp()
    {
        $utilisateur = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

        $this->actingAs($utilisateur);

        $response = $this->delete("/chirps/{$chirp->id}");

        $response->assertStatus(302);

        $this->assertDatabaseMissing('chirps', [
            'id' => $chirp->id,
        ]);
    }

    // exercice 6
    public function test_un_utilisateur_ne_peut_pas_modifier_ou_supprimer_le_chirp_d_un_autre_utilisateur()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);

        $responseUpdate = $this->put("/chirps/{$chirp->id}", [
            'message' => 'Modification interdite',
        ]);
        $responseUpdate->assertStatus(403);

        $responseDelete = $this->delete("/chirps/{$chirp->id}");
        $responseDelete->assertStatus(403);
    }

    // exercice 7
    public function test_un_chirp_mis_a_jour_ne_peut_pas_avoir_un_contenu_vide()
    {
        $utilisateur = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

        $this->actingAs($utilisateur);

        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['message']);
    }

    public function test_un_chirp_mis_a_jour_ne_peut_pas_depasse_255_caracteres()
    {
        $utilisateur = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

        $this->actingAs($utilisateur);

        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors(['message']);
    }



    //exercice 8
    public function test_un_utilisateur_ne_peut_pas_creer_plus_de_10_chirps()
{
    $utilisateur = User::factory()->create();
    Chirp::factory()->count(10)->create(['user_id' => $utilisateur->id]);

    $this->actingAs($utilisateur);
    $reponse = $this->post('/chirps', ['message' => 'Chirp supplémentaire']);

    $reponse->assertStatus(302); 

    
}
    //exercice 9 

public function test_afficher_seulement_les_chirps_recents()
{
    // Chirps récents
    $recents = Chirp::factory()->count(3)->create(['created_at' => now()]);

    // Chirps anciens
    $anciens = Chirp::factory()->count(2)->create(['created_at' => now()->subDays(8)]);

    $reponse = $this->get('/');

    foreach ($recents as $chirp) {
        $reponse->assertSee($chirp->content);
    }

    foreach ($anciens as $chirp) {
        $reponse->assertDontSee($chirp->content);
    }
}

    
}