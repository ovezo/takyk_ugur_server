<?php

namespace App\Http\Admin\Controllers;

use App\Http\Requests\MrouteRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MrouteCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MrouteCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\Domain\Routes\Models\Mroute::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/mroute');
        CRUD::setEntityNameStrings('mroute', 'mroutes');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::column('id');
        CRUD::column('name');
        $this->crud->addButtonFromModelFunction('line', 'draw_line', 'drawLine', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'draw_line_back', 'drawLineBack', 'beginning');

        //CRUD::column('start_coords');
        //CRUD::column('end_coords');
        //CRUD::column('created_at');
       // CRUD::column('updated_at');

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
        CRUD::setValidation(MrouteRequest::class);

        $this->crud->addFields([
            [
                'name'      => 'name',
                'label'     => 'Name',
                'type' => 'text'
            ],
            [   // Table
                'name'            => 'start_coords',
                'label'           => 'Starting coordinations',
                'type'            => 'table',
                'entity_singular' => 'option', // used on the "Add X" button
                'columns'         => [
                    'lat'  => 'lat',
                    'lng'  => 'lng',
                    'is_stop' => 'is_stop',
                    'index' => 'index',
                ],
                'max' => 1000, // maximum rows allowed in the table
                'min' => 0, // minimum rows allowed in the table
            ],
            [   // Table
                'name'            => 'end_coords',
                'label'           => 'Back coordinations',
                'type'            => 'table',
                'entity_singular' => 'option', // used on the "Add X" button
                'columns'         => [
                    'lat'  => 'lat',
                    'lng'  => 'lng',
                    'is_stop' => 'is_stop',
                    'index' => 'index',
                ],
                'max' => 1000, // maximum rows allowed in the table
                'min' => 0, // minimum rows allowed in the table
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
}
