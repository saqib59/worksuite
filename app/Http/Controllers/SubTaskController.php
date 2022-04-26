<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\SubTask\StoreSubTask;
use App\Models\SubTask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubTaskController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->subTask = SubTask::with(['files'])->findOrFail($id);
        return view('tasks.sub_tasks.edit', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->subTask = SubTask::with(['files'])->findOrFail($id);
        return view('tasks.sub_tasks.detail', $this->data);
    }

    /**
     * @param StoreSubTask $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreSubTask $request)
    {
        $this->addPermission = user()->permission('add_sub_tasks');
        $task = Task::findOrFail($request->task_id);
        $taskUsers = $task->users->pluck('id')->toArray();

        abort_403(!(
            $this->addPermission == 'all'
            || ($this->addPermission == 'added' && $task->added_by == user()->id)
            || ($this->addPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->addPermission == 'added' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
        ));
        
        $subTask = new SubTask();
        $subTask->title = $request->title;
        $subTask->task_id = $request->task_id;
        $subTask->description = str_replace('<p><br></p>', '', trim($request->description));

        if ($request->start_date != '' && $request->due_date != '') {
            $subTask->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
            $subTask->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        }

        $subTask->assigned_to = $request->user_id ? $request->user_id : null;

        $subTask->save();

        $task = $subTask->task;
        $this->logTaskActivity($task->id, $this->user->id, 'subTaskCreateActivity', $task->board_column_id, $subTask->id);
        return Reply::successWithData(__('messages.subTaskAdded'), [ 'subTaskID' => $subTask->id]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subTask = SubTask::findOrFail($id);
        SubTask::destroy($id);

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('messages.subTaskDeleted'), ['view' => $view]);
    }

    public function changeStatus(Request $request)
    {
        $subTask = SubTask::findOrFail($request->subTaskId);
        $subTask->status = $request->status;
        $subTask->save();

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $this->logTaskActivity($this->task->id, user()->id, 'subTaskUpdateActivity', $this->task ->board_column_id, $subTask->id);

        $view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    /**
     * @param StoreSubTask $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreSubTask $request, $id)
    {
        $subTask = SubTask::findOrFail($id);
        $subTask->title = $request->title;
        $subTask->description = str_replace('<p><br></p>', '', trim($request->description));

        if ($request->start_date != '') {
            $subTask->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        }

        if ($request->due_date != '') {
            $subTask->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        }

        $subTask->assigned_to = $request->user_id ? $request->user_id : null;

        $subTask->save();

        $task = $subTask->task;
        $this->logTaskActivity($task->id, $this->user->id, 'subTaskUpdateActivity', $task->board_column_id, $subTask->id);

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('messages.subTaskUpdated'), ['view' => $view]);
    }

}
