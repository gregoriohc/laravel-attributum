<?php

namespace Gregoriohc\Attributum;

trait ValidatesAttributable
{
    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = parent::getValidatorInstance();

        collect($this->getModelAttributesRules())->map(function ($rules, $attribute) use ($validator) {
            $validator->mergeRules($attribute, $rules);
        });

        return $validator;
    }

    /**
     * @return string
     */
    private function getValidatorModelClass()
    {
        if (isset($this->modelClass)) {
            return $this->modelClass;
        }

        return
            config('attributum.models_namespace', 'App') .
            '\\' .
            substr(class_basename($this), 0, -strlen('Controller'));
    }

    /**
     * @return array
     */
    private function getModelAttributesRules()
    {
        $modelClass = $this->getValidatorModelClass();

        if (Manager::hasModelAttributes($modelClass)) {
            return Manager::modelAttributes($modelClass)
                ->mapWithKeys(function ($item) {
                    return [$item->name => isset($item->options['rules']) && is_string($item->options['rules']) ? $item->options['rules'] : ''];
                })->toArray();
        }

        return [];
    }
}