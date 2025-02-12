<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Question extends Model
{
    protected $fillable = [
        'text',
        'type',
        'options',
        'order',
        'is_required',
        'category'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean'
    ];

    /**
     * Get the answers for the question.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Scope a query to only include required questions.
     */
    public function scopeRequired(Builder $query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope a query to order questions by their defined order.
     */
    public function scopeInOrder(Builder $query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get questions by category.
     */
    public function scopeByCategory(Builder $query, string $category)
    {
        return $query->where('category', $category);
    }
}