<?php

namespace Gregoriohc\Attributum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelAttributeValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'model_attribute_id',
        'value',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attributable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modelAttribute()
    {
        return $this->belongsTo(ModelAttribute::class);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return config('attributum.model_attribute_value_table', parent::getTable());
    }
}