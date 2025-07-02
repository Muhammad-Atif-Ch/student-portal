<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class LanguageStatusTest extends TestCase
{
  use RefreshDatabase;

  protected $admin;

  protected function setUp(): void
  {
    parent::setUp();

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
  }

  /** @test */
  public function it_can_update_language_status()
  {
    // Create a language
    $language = Language::factory()->create(['status' => false]);

    $response = $this->actingAs($this->admin)
      ->postJson(route('admin.language.update.status'), [
        'id' => $language->id,
        'status' => true
      ]);

    $response->assertStatus(200)
      ->assertJson([
        'status' => 'success',
        'message' => 'Language status updated successfully'
      ]);

    $this->assertTrue(Language::find($language->id)->status);
  }

  /** @test */
  public function it_validates_required_fields()
  {
    $response = $this->actingAs($this->admin)
      ->postJson(route('admin.language.update.status'), []);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['id', 'status']);
  }

  /** @test */
  public function it_validates_language_exists()
  {
    $response = $this->actingAs($this->admin)
      ->postJson(route('admin.language.update.status'), [
        'id' => 999,
        'status' => true
      ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['id']);
  }

  /** @test */
  public function it_requires_authentication()
  {
    $language = Language::factory()->create();

    $response = $this->postJson(route('admin.language.update.status'), [
      'id' => $language->id,
      'status' => true
    ]);

    $response->assertStatus(401);
  }
}