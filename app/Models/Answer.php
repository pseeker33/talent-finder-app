<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'answer',
        'additional_context'
    ];

    protected $casts = [
        'answer' => 'array',
        'additional_context' => 'array'
    ];

    /**
     * Get the user that owns the answer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the question that owns the answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the formatted answer based on question type.
     */
    public function getFormattedAnswerAttribute()
    {
        switch ($this->question->type) {
            case 'multiple_choice':
                return is_array($this->answer) ? implode(', ', $this->answer) : $this->answer;
            case 'boolean':
                return $this->answer ? 'Yes' : 'No';
            default:
                return $this->answer;
        }
    }
}