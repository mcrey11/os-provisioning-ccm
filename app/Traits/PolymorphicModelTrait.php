<?php

namespace App\Traits;

/**
 * Trait for handling polymorphic model instantiation based on qualified_model_class field.
 * This ensures that the correct derived class is instantiated instead of the base class.
 */
trait PolymorphicModelTrait
{
    /**
     * Find a single model with polymorphic instantiation.
     * This ensures that the correct derived class is instantiated
     * instead of the base class.
     *
     * @param  int  $id  The model ID
     * @param  array  $withRelations  Additional relationships to load
     * @param  string  $baseClass  The base class to use for initial query
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findPolymorphicModel($id, $withRelations = [], $baseClass = null)
    {
        $baseClass = $baseClass ?: static::class;

        // First get the base model record with relationships
        $model = $baseClass::with($withRelations)->find($id);

        if (! $model) {
            return null;
        }

        return self::instantiatePolymorphicModel($model);
    }

    /**
     * Find a single model with polymorphic instantiation or fail.
     * This ensures that the correct derived class is instantiated
     * instead of the base class.
     *
     * @param  int  $id  The model ID
     * @param  array  $withRelations  Additional relationships to load
     * @param  string  $baseClass  The base class to use for initial query
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findPolymorphicModelOrFail($id, $withRelations = [], $baseClass = null)
    {
        $model = self::findPolymorphicModel($id, $withRelations, $baseClass);

        if (! $model) {
            $baseClass = $baseClass ?: static::class;
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel($baseClass, $id);
        }

        return $model;
    }

    /**
     * Get multiple models with polymorphic instantiation.
     * This ensures that the correct derived classes are instantiated
     * instead of the base class.
     *
     * @param  array  $withRelations  Additional relationships to load
     * @param  string  $baseClass  The base class to use for initial query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPolymorphicModels($withRelations = [], $baseClass = null)
    {
        $baseClass = $baseClass ?: static::class;

        // Get the base model records with relationships
        $models = $baseClass::with($withRelations)->get();

        return $models->map(function ($model) {
            return self::instantiatePolymorphicModel($model);
        });
    }

    /**
     * Instantiate a polymorphic model from a base model instance.
     * This is the core logic for creating the correct derived class instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  The base model instance
     * @return \Illuminate\Database\Eloquent\Model The polymorphic model instance
     */
    public static function instantiatePolymorphicModel($model)
    {
        // Check if this model has a qualified_model_class set
        if (isset($model->qualified_model_class) &&
            $model->qualified_model_class &&
            class_exists($model->qualified_model_class)) {
            // Create a new instance of the derived class
            $instance = new $model->qualified_model_class();

            // Set the table name to ensure it uses the correct table
            $instance->setTable($model->getTable());

            // Set the attributes
            $instance->setRawAttributes($model->getAttributes(), true);

            // Set the original attributes
            $instance->syncOriginal();

            // Set the loaded relationships
            foreach ($model->getRelations() as $relation => $value) {
                $instance->setRelation($relation, $value);
            }

            return $instance;
        }

        return $model;
    }
}
