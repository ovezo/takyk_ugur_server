<?php

namespace App\Http\Admin\Controllers;

use Domain\Stops\Requests\StopRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Domain\Stops\Models\Stop;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * Class StopCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StopCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\Domain\Stops\Models\Stop::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/stop');
        CRUD::setEntityNameStrings('stop', 'stops');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addButtonFromModelFunction('line', 'locate', 'locate', 'beginning');
        $this->crud->addColumns([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name'
            ],
            [
                'name' => 'is_endpoint',
                'type' => 'radio',
                'label' => 'Is endpoint?',
                'options' => [
                    0 => 'No',
                    1 => 'Yes'
                ]
            ]
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(StopRequest::class);

        $this->crud->addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name'
            ],
            [
                'name' => 'is_endpoint',
                'type' => 'checkbox',
                'label' => 'Is endpoint?'
            ],
            [
                'name'  => 'location', // do not change this
                'type'  => 'point', // do not change this
                'label' => "Coordinates"
            ],

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

    public function store()
    {
        $req_body = $this->crud->getRequest()->all();

        $geodata = explode(',',$req_body['location']);
        $lat = $geodata[0];
        $lng = $geodata[1];
        $this->crud->getRequest()->request->remove('location');

        $response = $this->traitStore();

        $entryID = $this->data['entry']->id;

        $stop = Stop::find($entryID);
        $stop->location = new Point($lat, $lng, 4326);
        $stop->save();

        return $response;
    }

    public function update()
    {
        $req_body = $this->crud->getRequest()->all();

        if($req_body['location']){
            $geodata = explode(',', $req_body['location']);
            $lat = $geodata[0];
            $lng = $geodata[1];

            $this->crud->getRequest()->request->remove('location');

            $response = $this->traitUpdate();

            $entryID = $this->data['entry']->id;

            $stop = Stop::find($entryID);
            $stop->location = new Point($lat, $lng, 4326);
            $stop->save();
        }

        $response = $this->traitUpdate();

        return $response;
    }
}
