Hi {{$recipient->name}}, <br/>
<br/>
The tasks below are due:
<br/><br/>
<br/>
<br/>
<b>Tasks List:</b>
<table border='1'>
  <thead>
    <th>Task</th>
    <th>Assigned</th>
    <th>Customer</th>
    <th>Job</th>
    <th>Due</th>
  </thead>
    @foreach ($tasks as $task)
    <tr>
      <td>{{ $task->subject }}</td>
      <td>{{ $task->user_assigned_name }}</td>
      <td>{{ $task->cust_name }}</td>
      <td>@php if(!$task->user_job_id) echo '--no job--'; else echo  $task->user_job_id; @endphp</td>
      <td>{{ $task->due }}</td>
    </tr>
    @endforeach
</table>
