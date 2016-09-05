<?php

namespace Gregoriohc\Attributum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelAttribute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'attributable_type',
        'name',
        'type',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modelAttributeValues()
    {
        return $this->hasMany(ModelAttributeValue::class);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return config('attributum.model_attribute_table', parent::getTable());
    }
}