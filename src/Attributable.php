<?php

namespace Gregoriohc\Attributum;

trait Attributable
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootAttributable()
    {
        // Eager load attributes values and attributes list
        static::loaded(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model|Attributable $model */
            $model->load(['modelAttributeValues']);
            $model->modelAttributes();
        });
    }

    /**
     * @inheritdoc
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $instance = parent::newFromBuilder($attributes, $connection);

        /** @var \Illuminate\Database\Eloquent\Model $instance */
        $instance->fireModelEvent('loaded');

        return $instance;
    }

    /**
     * Register a loaded model event with the dispatcher.
     *
     * @param  \Closure|string $callback
     * @param  int $priority
     * @return void
     */
    public static function loaded($callback, $priority = 0)
    {
        static::registerModelEvent('loaded', $callback, $priority);
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->issetModelAttribute($key)) {
            $value = $this->getModelAttributeValue($key);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function setAttribute($key, $value)
    {
        if (!is_null(parent::getAttribute($key))) {
            parent::setAttribute($key, $value);
        } elseif ($this->issetModelAttribute($key)) {
            $this->setModelAttributeValue($key, $value);
        }

        return $this;
    }

    /**
     * Determine if the attribute exits
     *
     * @param string $key
     * @return bool
     */
    private function issetModelAttribute($key)
    {
        return $this->modelAttributes()->contains('name', $key);
    }

    /**
     * Get the attribute value
     *
     * @param string $key
     * @return mixed|null
     */
    private function getModelAttributeValue($key)
    {
        $attributeInfo = $this->modelAttributes()->where('name', $key)->first();

        if ($attribute = $this->modelAttributeValues->where('model_attribute_id', $attributeInfo->id)->first()) {
            return Manager::castValueGet(
                $attribute->value,
                $attributeInfo->type,
                $attributeInfo->options
            );
        } elseif ($attributeInfo) {
            return Manager::castValueGet(
                is_array($attributeInfo->options) && array_key_exists('default', $attributeInfo->options) ? $attributeInfo->options['default'] : null,
                $attributeInfo->type,
                $attributeInfo->options
            );
        }

        return null;
    }

    /**
     * Set the attribute value
     *
     * @param string $key
     * @param mixed $value
     */
    private function setModelAttributeValue($key, $value)
    {
        $attributeInfo = $this->modelAttributes()->where('name', $key)->first();

        $value = Manager::castValueSet($value, $attributeInfo->type, $attributeInfo->options);

        if ($attribute = $this->modelAttributeValues->where('model_attribute_id', $attributeInfo->id)->first()) {
            $attribute->value = $value;
            $attribute->save();
        } else {
            $this->modelAttributeValues()->create([
                'model_attribute_id' => $attributeInfo->id,
                'value' => $value,
            ]);
        }
    }

    /**
     * Get the list of model attributes
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function modelAttributes()
    {
        return Manager::modelAttributes(static::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function modelAttributeValues()
    {
        return $this->morphMany(ModelAttributeValue::class, 'attributable');
    }
}