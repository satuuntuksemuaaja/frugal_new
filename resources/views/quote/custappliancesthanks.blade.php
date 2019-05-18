@extends('layouts.locked', ['title' => 'Appliance Configuration | Thanks!'])

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class='alert alert-success'>
          Thank you for supplying your appliance information. You can <a href="#" onclick="javascript:window.close();">close</a> this window.
        </div>
    </div>
</div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){

});

</script>
@endsection
