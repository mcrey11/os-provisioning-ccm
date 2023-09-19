<?php
/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Http\Controllers;

use App\BaseModel;
use App\GuiLog;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class GuiLogController extends BaseController
{
    protected $index_create_allowed = false;
    protected $index_delete_allowed = false;
    protected $edit_view_save_button = false;

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $fields = [
            ['form_type' => 'text', 'name' => 'username', 'description' => 'Username'],
            ['form_type' => 'text', 'name' => 'method', 'description' => 'Method'],
            ['form_type' => 'text', 'name' => 'model', 'description' => 'Model'],
            ['form_type' => 'text', 'name' => 'model_id', 'description' => 'ID'],
            ['form_type' => 'textarea', 'name' => 'text', 'description' => 'Changed Attributes'],
        ];

        if ($buttonField = $this->restoreButtonField($model)) {
            $fields[] = $buttonField;
        }

        return $fields;
    }

    /**
     * Edit view field with link (button) to changed/created model or restore button
     *
     * @return array
     */
    private function restoreButtonField($model)
    {
        $models = BaseModel::get_models();
        $isForceDeleteDisabled = ! $model->getDefaultProperty($models[$model->model], 'forceDeleting');
        $isModelTrashed = $isForceDeleteDisabled ?
            $models[$model->model]::withTrashed()->find($model->model_id)->trashed() :
            false;

        $unRestoreables = ['Invoice', 'SettlementRun'];
        $isRestorable = ! in_array($model->model, $unRestoreables);

        // addition in edit view to create link for restoring deleted models
        if ($isModelTrashed) {
            $field = [
                'form_type' => 'html',
                'name' => 'deleted_at',
                'description' => trans('view.restore'),
            ];

            $route = null;
            $text = $model->model;
            if ($isRestorable) {
                $route = route('Guilog.restore', [$model->id]);
            } else {
                $text .= ' '.trans('messages.canNotBeRestored');
            }

            $field['html'] = view('GuiLog.restoreButton', compact('route', 'text'));

            return $field;
        }

        // add link of changed Model in edit view - Note: check if route exists is necessary because CccUser.edit is
        // not available for instance
        if ($models && \Route::getRoutes()->hasNamedRoute($model->model.'.edit') && ! $isModelTrashed) {
            $route = route($model->model.'.edit', [$model->model_id]);
            $text = $model->model.' '.$model->model_id;

            $field = [
                'form_type' => 'html',
                'name' => 'link',
                'description' => 'Link',
                'html' => view('GuiLog.restoreButton', compact('route', 'text')),
            ];

            return $field;
        }
    }

    /**
     * Restore a soft-deleted model
     *
     * @param id GuiLog
     *
     * @author Roy Schneider
     */
    public function restoreModel($id)
    {
        $modelArray = BaseModel::get_models();
        $guilog = GuiLog::find($id);
        $modelToRestore = $modelArray[$guilog->model]::withTrashed()->find($guilog->model_id);
        $modelToRestore->restore($guilog->model);

        if (\Route::has($guilog->model.'.edit')) {
            return redirect()->route($guilog->model.'.edit', [$guilog->model_id]);
        } else {
            return redirect()->route('GuiLog.index');
        }
    }

    public function filter($id, Request $request)
    {
        $uri = explode('/', $request->getRequestUri());
        $routeName = NamespaceController::get_route_name();
        $modelName = $uri[count($uri) - 3];

        $request_query = GuiLog::where('model', $modelName)
            ->where('model_id', '=', $id)
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::make($request_query)
            ->addColumn('responsive', '')
            ->setRowClass(function ($guilog) {
                $lookup = [
                    'created' => 'success',
                    'deleted' => 'danger',
                ];

                return $lookup[$guilog->method] ?? 'info';
            })
            ->editColumn('created_at', function ($guilog) use ($routeName) {
                return '<a href="'.route($routeName.'.edit', $guilog->id).
                        '" title="'.str_replace(', ', '&#013;', str_replace('"', '\'', $guilog->text)).'"><strong>'.
                        $guilog->view_icon().$guilog->created_at.'</strong></a>';
            })
            ->rawColumns(['created_at'])
            ->make();
    }
}
