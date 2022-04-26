<?php

namespace App\Http\Requests\Tasks;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;
use App\Models\Project;
use App\Models\Task;

class StoreTask extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = global_setting();
        $user = user();
        $rules = [
            'heading' => 'required',
            'start_date' => 'required|date_format:"' . $setting->date_format . '"',
            'priority' => 'required'
        ];

        if(!$this->has('without_duedate'))
        {
            $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date';
        }

        if (request()->has('project_id') && request()->project_id != 'all' && request()->project_id != '') {
            $project = Project::findOrFail(request()->project_id);
            $startDate = $project->start_date->format($setting->date_format);
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;
        }
        else {
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format;
        }

        if ($this->has('dependent') && $this->dependent_task_id != '') {
            $dependentTask = Task::find($this->dependent_task_id);
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:"' . $dependentTask->due_date->format($setting->date_format) . '"';
        }

        $rules['user_id.0'] = 'required_with:is_private';
        $rules['dependent_task_id'] = 'required_with:dependent';

        if ($this->has('repeat')) {
            $rules['repeat_cycles'] = 'required|numeric';
        }

        if ($this->has('set_time_estimate')) {
            $rules['estimate_hours'] = 'required|integer|min:0';
            $rules['estimate_minutes'] = 'required|integer|min:0';
        }

        if (request()->get('custom_fields_data')) {
            $fields = request()->get('custom_fields_data');

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);
                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes' && (is_null($value) || $value == '')) {
                    $rules['custom_fields_data['.$key.']'] = 'required';
                }
            }

        }

        return $rules;
    }

    public function messages()
    {
        return [
            'project_id.required' => __('messages.chooseProject'),
            'due_date.after_or_equal' => __('messages.taskDateValidation')
        ];
    }

    public function attributes()
    {
        $attributes = [
            'user_id.0' => __('modules.tasks.assignTo'),
            'dependent_task_id' => __('modules.tasks.dependentTask')
        ];

        if (request()->get('custom_fields_data')) {
            $fields = request()->get('custom_fields_data');

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);
                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes') {
                    $attributes['custom_fields_data['.$key.']'] = $customField->label;
                }
            }

        }

        return $attributes;
    }

}
