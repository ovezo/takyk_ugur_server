<?php

namespace App\Http\Admin\Controllers;

use Domain\Routes\Requests\RouteStopRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class RouteStopCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RouteStopCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;


    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\Domain\Routes\Models\RouteStop::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/route-stop');
        CRUD::setEntityNameStrings('route stop', 'route stops');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // columns
        $this->crud->addColumns([
            [
                // 1-n relationship
                'label' => "Routes",
                'type' => "relationship",
                'name' => 'route_id',// the column that contains the ID of that connected entity;
                'entity' => 'route',// the method that defines the relationship in your Model
                'attribute' => "name",// foreign key attribute that is shown to user
                'model' => "Domain\Routes\Models\Route", // foreign key model
            ],
            [
                // 1-n relationship
                'label' => "Stops",
                'type' => "relationship",
                'name' => 'stop_id',// the column that contains the ID of that connected entity;
                'entity' => 'stop',// the method that defines the relationship in your Model
                'attribute' => "name",// foreign key attribute that is shown to user
                'model' => "Domain\Stops\Models\Stop", // foreign key model
            ],
            [
                'name' => 'index',
                'label' => 'Index',
                'type' => 'number'
            ]
        ]);
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(RouteStopRequest::class);

        //CRUD::setFromDb(); // fields
        $this->crud->addFields([
            [
                // 1-n relationship
                'label' => "Routes",
                'type' => "select",
                'name' => 'route_id',// the column that contains the ID of that connected entity;
                'entity' => 'route',// the method that defines the relationship in your Model
                'attribute' => "name",// foreign key attribute that is shown to user
                'model' => "Domain\Routes\Models\Route", // foreign key model
            ],
            [
                // 1-n relationship
                'label' => "Stops",
                'type' => "select",
                'name' => 'stop_id',// the column that contains the ID of that connected entity;
                'entity' => 'stop',// the method that defines the relationship in your Model
                'attribute' => "name",// foreign key attribute that is shown to user
                'model' => "Domain\Stops\Models\Stop", // foreign key model
            ],
            [
                'name' => 'index',
                'label' => 'Index',
                'type' => 'number'
            ]
        ]);
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
