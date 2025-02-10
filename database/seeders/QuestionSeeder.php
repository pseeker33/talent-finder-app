<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'type' => 'select',
                'question' => 'What type of business are you running?',
                'options' => json_encode([
                    'tech_startup' => 'Technology Startup',
                    'service_business' => 'Service Business',
                    'product_company' => 'Product Company',
                    'consulting' => 'Consulting Firm'
                ]),
                'step' => 1
            ],
            [
                'type' => 'select',
                'question' => 'What stage is your business in?',
                'options' => json_encode([
                    'idea' => 'Idea Stage',
                    'mvp' => 'MVP Stage',
                    'growth' => 'Growth Stage',
                    'scaling' => 'Scaling Stage'
                ]),
                'step' => 1
            ],
            // Añadir más preguntas según necesidad
        ];

        foreach ($questions as $question) {
            Question::create($question);
        }
    }
}