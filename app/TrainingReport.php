<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingReport extends TrainingObject
{

    protected $guarded = [];

    protected $casts = [
        'draft' => 'boolean'
    ];

    public function path()
    {
        return route('training.report.edit', ['report' => $this->id]);
    }

    public function training(){
        return $this->belongsTo(Training::class);
    }

    public function author(){
        return $this->belongsTo(User::class, 'written_by_id');
    }
}
