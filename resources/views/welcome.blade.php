@extends('layouts.main')
@section('content')
<div class="container-fluid">
  <div class="card-body card-colors row">
      <div class="col-lg-12 col-md-12 btn-group">

        <div class="dropdown">
          <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-search"></i> {{ $btnLabelCategory }}
          <span class="caret"></span></button>
          <ul class="dropdown-menu" style="z-index:9999;">
            <a href="/?category=appointments"><li><i class="fa fa-clock-o"></i> All Appointments</li></a>
            @foreach($locations as $location)
            <a href="/?category=appointments&location={{ $location->id }}"><li><i class="fa fa-clock-o"></i> {{ $location->name }} Appointments</li></a>
            @endforeach
            <a href="/?category=jobs"><li><i class="fa fa-cogs"></i> Jobs</li></a>
          </ul>
          </div>
          &nbsp;
          <div class="dropdown">
            <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-user"></i> {{ $btnLabelUser }}
            <span class="caret"></span></button>
            <ul class="dropdown-menu" style="z-index:9999;">
              @foreach($users as $user)
              <a href="/?user={{ $user->id }}"><li><i class="fa fa-user"></i> {{ $user->name }}</li></a>
              @endforeach
            </ul>
          </div>
          &nbsp;
          <div class="dropdown">
            <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-users"></i> {{ $btnLabelGroup }}
            <span class="caret"></span></button>
            <ul class="dropdown-menu" style="z-index:9999;">
              @foreach($groups as $group)
              <a href="/?group={{ $group->id }}"><li><i class="fa fa-users"></i> {{ $group->name }}</li></a>
              @endforeach
            </ul>
          </div>

      </div>
  </div>
    <div class="card-body card-colors row">
        <div class="col-lg-12 col-md-12">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.fc-time-grid .fc-bgevent, .fc-time-grid .fc-event {
    position: relative;
}
.fc-day-grid-event .fc-content {
    white-space: normal;
}
.fc-time-grid-event.fc-short .fc-content {
    white-space: normal;
}
</style>
@endsection

@section('javascript')
<script type="text/javascript">
$(function(){
  $('#calendar').fullCalendar({
                allDayDefault : false,
                header : true,
                header: {
                    left: 'prev,next today,title',
                    right: 'month,agendaWeek,agendaDay'
                },
                {!! $events !!}

              });
});
</script>
@endsection
