<?php

namespace App\Http\Admin\Controllers;

use Domain\Buses\Requests\BusRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class BusCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BusCrudController extends CrudController
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
        CRUD::setModel(\Domain\Buses\Models\Bus::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/bus');
        CRUD::setEntityNameStrings('bus', 'buses');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('car_number');
        //CRUD::column('location');
        CRUD::column('speed');
        CRUD::column('sc');
        CRUD::column('route_id');
        CRUD::column('prev_stop_id');
        CRUD::column('is_going_front');
        CRUD::column('is_onroad');

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(BusRequest::class);

	    $this->crud->addFields([
            [
                'name' => 'car_number',
                'label' => 'Car number',
                'type' => 'text'
            ]
        ]);
        //CRUD::field('car_number');
        //CRUD::field('location');
        //CRUD::field('speed');
        //CRUD::field('sc');
        //CRUD::field('route_id');
        //CRUD::field('prev_stop_id');
        //CRUD::field('is_going_front');
        //CRUD::field('is_onroad');
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
