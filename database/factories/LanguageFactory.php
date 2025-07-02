<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
  protected $model = Language::class;

  public function definition()
  {
    return [
      'family' => $this->faker->word,
      'name' => $this->faker->unique()->languageCode,
      'native_name' => $this->faker->word,
      'code' => $this->faker->unique()->languageCode,
      'code_2' => $this->faker->unique()->languageCode,
      'country_code' => $this->faker->countryCode,
      'status' => $this->faker->boolean,
    ];
  }
}